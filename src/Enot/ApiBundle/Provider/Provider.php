<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Provider;


use Enot\ApiBundle\Entity\Event;
use Enot\ApiBundle\Entity\Transportation;
use Enot\ApiBundle\Entity\Vehicle;
use Enot\ApiBundle\Model\ConnectorRequestModel;
use Enot\ApiBundle\Model\DriverModel;
use Enot\ApiBundle\Model\ProviderRequestModel;
use Enot\ApiBundle\Model\TrailerModel;
use Enot\ApiBundle\Model\VehicleModel;
use Enot\ApiBundle\Provider\RequestModels\GetDriversAvtoTrailers;
use Enot\ApiBundle\Provider\RequestModels\PutInfoCargoTransportation;
use Enot\ApiBundle\Provider\RequestModels\PutInfoCarState;
use Enot\ApiBundle\Provider\RequestModels\PutInforAppointment;

class Provider implements ProviderInterface
{
    private $connector;

    /**
     * Provider constructor.
     * @param ProviderRequestModel $request
     * @throws \Enot\LogBundle\Exceptions\LoggerException
     */
    public function __construct($request)
    {
        $connectorRequest = new ConnectorRequestModel();
        $connectorRequest->connectionString = $request->connectionString;
        $connectorRequest->httpClient = $request->httpClient;
        $connectorRequest->logger = $request->logger;

        $this->connector = new Connector($connectorRequest);
    }

    /**
     * @return array
     * @throws ProviderException
     */
    public function getDrivers()
    {
        $getDriversAvtoTrailers = new GetDriversAvtoTrailers();
        $getDriversAvtoTrailers->KeyChoice = 0;

        $request = new \stdClass();
        $request->body = $getDriversAvtoTrailers;

        $response = $this->connector->getDriversAvtoTrailers($request);

        $responseArray = [];
        if (property_exists($response, 'StrDr')) {
            foreach ($response->StrDr as $value) {
                $driver = new DriverModel();
                $driver->name = $value->LNS;
                $driver->externalId = $value->HC;
                $driver->phone = $value->PhoneNumber;

                $responseArray[] = $driver;
            }
        }

        return $responseArray;
    }

    /**
     * @return array
     * @throws ProviderException
     */
    public function getVehicles()
    {
        $getDriversAvtoTrailers = new GetDriversAvtoTrailers();
        $getDriversAvtoTrailers->KeyChoice = 1;

        $request = new \stdClass();
        $request->body = $getDriversAvtoTrailers;

        $response = $this->connector->getDriversAvtoTrailers($request);

        $responseArray = [];
        if (property_exists($response, 'StrAv')) {
            foreach ($response->StrAv as $value) {
                $vehicle = new VehicleModel();
                $vehicle->name = $value->AVTO;
                $vehicle->externalId = $value->HC;
                $vehicle->deviceMac = $value->MACADRESS;

                if ($vehicle->externalId && $vehicle->deviceMac) {
                    $responseArray[] = $vehicle;
                }
            }
        }

        return $responseArray;
    }

    /**
     * @return array
     * @throws ProviderException
     */
    public function getTrailers()
    {
        $getDriversAvtoTrailers = new GetDriversAvtoTrailers();
        $getDriversAvtoTrailers->KeyChoice = 2;

        $request = new \stdClass();
        $request->body = $getDriversAvtoTrailers;

        $response = $this->connector->getDriversAvtoTrailers($request);

        $responseArray = [];
        if (property_exists($response, 'StrTr')) {
            foreach ($response->StrTr as $value) {
                $trailer = new TrailerModel();
                $trailer->name = $value->TRAILER;
                $trailer->externalId = $value->HC;

                $responseArray[] = $trailer;
            }
        }

        return $responseArray;
    }

    /**
     * @param $event Event
     * @param $transportation Transportation
     * @param null $duration
     * @param null $date
     * @return mixed
     * @throws ProviderException
     */
    public function notifyEvent($event, $transportation, $duration = null, $date = null)
    {
        if($date) {
            $date = new \DateTime($date);
        }
        $putInfoCargoTransportation = new PutInfoCargoTransportation();
        $putInfoCargoTransportation->transportation_id = $transportation->getExternalId();
        $putInfoCargoTransportation->AVTOCODE = $transportation->getVehicle()->getExternalId();
        $putInfoCargoTransportation->DRIVERCODE = $transportation->getDriver()->getExternalId();
        $putInfoCargoTransportation->TRAILERCODE = $transportation->getTrailer()
            ? $transportation->getTrailer()->getExternalId()
            : null;

        if ($event->getId() == Event::ASSIGN_DRIVER) {
            $putInfoCargoTransportation->DateAccDriver = $this->convertDateToProviderString($date ? $date : new \DateTime('now'));
        }
        if ($event->getId() == Event::ARRIVAL_DELIVERY) {
            $putInfoCargoTransportation->DateArrDelivery = $this->convertDateToProviderString($date ? $date : new \DateTime('now'));
        }
        if ($event->getId() == Event::DEPARTURE_DELIVERY) {
            $putInfoCargoTransportation->DateDepDelivery = $this->convertDateToProviderString($date ? $date : new \DateTime('now'));
            $putInfoCargoTransportation->RemainingTime = $duration;
        }
        if ($event->getId() == Event::ARRIVAL_CLIENT) {
            $putInfoCargoTransportation->DateArrClient = $this->convertDateToProviderString($date ? $date : new \DateTime('now'));
        }
        if ($event->getId() == Event::DEPARTURE_CLIENT) {
            $putInfoCargoTransportation->DateDepClient = $this->convertDateToProviderString($date ? $date : new \DateTime('now'));
        }
        if ($event->getId() == Event::PASS_EMPTY) {
            $putInfoCargoTransportation->DatePassEmpty = $this->convertDateToProviderString($date ? $date : new \DateTime('now'));
        }

        $request = new \stdClass();
        $request->body = $putInfoCargoTransportation;

        $response = $this->connector->putInfoCargoTransportation($request);
        if ($response) {
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * @param Vehicle $vehicle
     * @param $status
     * @return bool
     * @throws ProviderException
     */
    public function sendOnLineStatus(Vehicle $vehicle, $status)
    {
        $putInfoCarState = new PutInfoCarState();
        $putInfoCarState->AVTOCODE = $vehicle->getExternalId();
        $putInfoCarState->State = $status;

        $request = new \stdClass();
        $request->body = $putInfoCarState;

        $response = $this->connector->putInfoCarState($request);

        if ($response) {
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * @param Transportation $transportation
     * @param bool $rejection
     * @return bool
     * @throws ProviderException
     */
    public function sendDriverNotAssigned(Transportation $transportation, $rejection = false)
    {
        $putInforAppointment = new PutInforAppointment();
        $putInforAppointment->REJECTION = $rejection;
        $putInforAppointment->transportation_id = $transportation->getExternalId();
        $putInforAppointment->AVTOCODE = $rejection ? "" : $transportation->getVehicle()->getExternalId();
        $putInforAppointment->DRIVERCODE = $rejection ? "" : $transportation->getDriver()->getExternalId();
        $request = new \stdClass();
        $request->body = $putInforAppointment;

        $response = $this->connector->putInforAppointment($request);
        if ($response) {
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * @param \DateTime $dateTime
     * @return string
     */
    private function convertDateToProviderString(\DateTime $dateTime)
    {
        return $dateTime->format('YmdHis');
    }

}
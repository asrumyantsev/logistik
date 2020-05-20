<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Services;


use Doctrine\ORM\EntityManager;
use Enot\ApiBundle\Entity\ContainerType;
use Enot\ApiBundle\Entity\Driver;
use Enot\ApiBundle\Entity\Event;
use Enot\ApiBundle\Entity\Terminal;
use Enot\ApiBundle\Entity\Trailer;
use Enot\ApiBundle\Entity\Transportation;
use Enot\ApiBundle\Entity\TransportationEventHistory;
use Enot\ApiBundle\Entity\User;
use Enot\ApiBundle\Entity\Vehicle;
use Enot\ApiBundle\Event\DriverEvent;
use Enot\ApiBundle\Event\TransportationFieldEvent;
use Enot\ApiBundle\Model\CreateTransportationRequestModel;
use Enot\ApiBundle\Model\ProviderRequestModel;
use Enot\ApiBundle\Model\VehicleModel;
use Enot\ApiBundle\Provider\Provider;
use Enot\ApiBundle\Services\Exceptions\TransportationException;
use Enot\ApiBundle\Services\Main\HttpClientInterface;
use Enot\ApiBundle\Utils\EnotError;
use Enot\LogBundle\Utils\Logger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

class TransportationManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var ConfigurationManager
     */
    private $configurationManager;
    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var AutoSetterManager
     */
    private $autoSetterManager;

    private $queryMode = true;

    /**
     * TransportationManager constructor.
     * @param EntityManager $entityManager
     * @param ConfigurationManager $configurationManager
     * @param UserManager $userManager
     * @param Logger $logger
     * @param HttpClientInterface $httpClient
     * @param EventDispatcherInterface $dispatcher
     * @param AutoSetterManager $autoSetterManager
     * @throws \Enot\LogBundle\Exceptions\LoggerException
     */
    public function __construct(EntityManager $entityManager,
                                ConfigurationManager $configurationManager,
                                UserManager $userManager,
                                Logger $logger,
                                HttpClientInterface $httpClient,
                                EventDispatcherInterface $dispatcher,
                                AutoSetterManager $autoSetterManager)
    {
        $this->entityManager = $entityManager;
        $this->configurationManager = $configurationManager;
        $this->userManager = $userManager;
        $this->httpClient = $httpClient;
        $this->autoSetterManager = $autoSetterManager;

        $providerRequest = new ProviderRequestModel();
        $providerRequest->logger = $logger;
        $providerRequest->httpClient = $httpClient;
        $providerRequest->connectionString = $this->configurationManager->getConnectionString();

        $this->provider = new Provider($providerRequest);
        $this->dispatcher = $dispatcher;


    }

    /**
     * @return \Enot\ApiBundle\Repository\TransportationRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository('EnotApiBundle:Transportation');
    }

    /**
     * @return \Enot\ApiBundle\Repository\DriverRepository
     */
    public function getDriverRepository()
    {
        return $this->entityManager->getRepository('EnotApiBundle:Driver');
    }

    /**
     * @return \Enot\ApiBundle\Repository\VehicleRepository
     */
    public function getVehicleRepository()
    {
        return $this->entityManager->getRepository('EnotApiBundle:Vehicle');
    }

    /**
     * @return \Enot\ApiBundle\Repository\TrailerRepository
     */
    public function getTrailerRepository()
    {
        return $this->entityManager->getRepository('EnotApiBundle:Trailer');
    }

    /**
     * @return \Enot\ApiBundle\Repository\ContainerTypeRepository
     */
    public function getContainerTypeRepository()
    {
        return $this->entityManager->getRepository('EnotApiBundle:ContainerType');
    }

    /**
     * @param $driverId
     * @return Transportation[]
     * @throws TransportationException
     */
    public function getAllNotAssignedTransportation($driverId)
    {
        /** @var Driver $driver */
        $driver = $this->getDriverRepository()->find($driverId);
        if (!$driver) {
            throw new TransportationException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        return $this->getRepository()->findAllNotAssignedTransportation($driver);
    }

    /**
     * Marks the Transportation of canceled by reason
     *
     * @param $transportationId
     * @param $reason
     * @return Transportation
     * @throws TransportationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function cancelTransportation($transportationId, $reason)
    {
        /** @var Transportation $transportation */
        $transportation = $this->getRepository()->find($transportationId);
        if (!$transportation) {
            throw new TransportationException(EnotError::TRANSPORTATION_NOT_FOUND, '', Response::HTTP_BAD_REQUEST);
        }

        if (!$reason) {
            throw new TransportationException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        $transportation->setCanceled(true);
        $transportation->setCanceledAt(new \DateTime('now'));
        $transportation->setCancelReason($reason);

        $transportation->setArchived(true);
        $transportation->setArchivedAt(new \DateTime('now'));

        $this->entityManager->persist($transportation);
        $this->entityManager->flush();

        //@todo notify to Deltrans

        return $transportation;
    }

    /**
     * Marks the Transportation of archived and finished
     *
     * @param $transportationId
     * @param null $user
     * @return Transportation
     * @throws TransportationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Enot\ApiBundle\Provider\ProviderException
     */
    public function finishTransportation($transportationId, $user = null)
    {
        /** @var Transportation $transportation */
        $transportation = $this->getRepository()->find($transportationId);
        if (!$transportation) {
            throw new TransportationException(EnotError::TRANSPORTATION_NOT_FOUND, '', Response::HTTP_BAD_REQUEST);
        }

        if ($transportation->getLastEvent() != Event::PASS_EMPTY) {
            for ($eventCode = $transportation->getLastEvent(); $eventCode >= Event::PASS_EMPTY; $eventCode++) {
                $this->eventTransportation($transportationId, $eventCode, null, $user);
            }
        }
        $this->eventTransportation($transportationId, Event::FINISH, null, $user);

        $transportation->setSuccessfullyCompleted(true);
        $transportation->setCompletedAt(new \DateTime('now'));
        $transportation->setArchived(true);
        $transportation->setArchivedAt(new \DateTime('now'));
        $transportation->setLastEvent(Event::FINISH);

        $this->entityManager->persist($transportation);
        $this->entityManager->flush();

        return $transportation;
    }

    /**
     * Retrieves the Transportation from the archive
     * (Only for non-completed Transportation)
     *
     * @param $transportationId
     * @return Transportation
     * @throws TransportationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function unArchiveTransportation($transportationId)
    {
        /** @var Transportation $transportation */
        $transportation = $this->getRepository()->find($transportationId)
            ? $this->getRepository()->find($transportationId)
            : $this->getRepository()->findOneByExternalId($transportationId);

        if (!$transportation) {
            throw new TransportationException(EnotError::TRANSPORTATION_NOT_FOUND, '', Response::HTTP_BAD_REQUEST);
        }

        if ($transportation->isSuccessfullyCompleted()) {
            throw new TransportationException(EnotError::TRANSPORTATION_ALREADY_FINISHED, '', Response::HTTP_BAD_REQUEST);
        }

        $transportation->setArchived(false);
        $transportation->setArchivedAt(null);

        $transportation->setAssigned(false);
        $transportation->setAssignedAt(null);


        $this->entityManager->persist($transportation);
        $this->entityManager->flush();

        return $transportation;
    }

    /**
     * @param $transportationId
     * @param $driverId
     * @return Transportation
     * @throws TransportationException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Enot\ApiBundle\Provider\ProviderException
     * @throws \Doctrine\ORM\ORMException
     */
    public function assignTransportation($transportationId, $driverId)
    {
        /** @var Transportation $transportation */
        $transportation = $this->getRepository()->find($transportationId);
        /** @var Driver $driver */
        $driver = $this->getDriverRepository()->find($driverId);
        if (!$driver || !$transportation) {
            throw new TransportationException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        if ($transportation->getDriver() !== $driver) {
            throw new TransportationException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        if ($transportation->isAssigned()) {
            throw new TransportationException(EnotError::TRANSPORTATION_ALREADY_ASSIGNED, '', Response::HTTP_BAD_REQUEST);
        }

        $transportation->setAssigned(true);
        $transportation->setAssignedAt(new \DateTime('now'));

        $this->entityManager->persist($transportation);
        $this->entityManager->flush();

        //notify about assign
        $this->eventTransportation($transportationId, Event::ASSIGN_DRIVER);

        return $transportation;
    }


    /**
     * @param $transportationId
     * @param $eventId
     * @param null $date
     * @param User $user
     * @return string
     * @throws TransportationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Enot\ApiBundle\Provider\ProviderException
     */
    public function eventTransportation($transportationId, $eventId, $date = null, $user = null)
    {
        /** @var Transportation $transportation */
        $transportation = $this->getRepository()->find($transportationId);

        /** @var Event $event */
        $event = $this->entityManager->getRepository('EnotApiBundle:Event')->find($eventId);

        if (!$event || !$transportation) {
            throw new TransportationException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        if ($transportation->getLastEvent() >= $eventId) {
            throw new TransportationException(EnotError::TRANSPORTATION_POINT_WAS_PASSED, '', Response::HTTP_BAD_REQUEST);
        }

        //get duration for reach from Event::DEPARTURE_DELIVERY to Event::ARRIVAL_CLIENT
        $duration = null;
        if ($eventId == Event::DEPARTURE_DELIVERY) {
            $point1 = $transportation->getFromAddressGis()
                ? $transportation->getFromAddressGis()['latitude'] . ',' . $transportation->getFromAddressGis()['longitude']
                : null;

            $point2 = $transportation->getToAddressGis()
                ? $transportation->getToAddressGis()['latitude'] . ',' . $transportation->getToAddressGis()['longitude']
                : null;

            $duration = $this->getDuration($point1, $point2);
        }
        $response = $this->provider->notifyEvent($event, $transportation, $duration, $date);

        if ($response) {
            $result = ResponseManager::STATUS_SUCCESS;

            $transportation->setLastEvent($eventId);
            $history = new TransportationEventHistory();
            $history->setTransportation($transportation);
            $history->setEvent($this->entityManager->getRepository("EnotApiBundle:Event")->find($eventId));
            $history->setDate(new \DateTime());
            $history->setUser($user);
            $this->entityManager->persist($transportation);
            $this->entityManager->persist($history);
            $this->entityManager->flush();
        } else {
            $result = ResponseManager::STATUS_FAIL;
        }

        return $result;
    }

    /**
     * @param $key
     * @return int
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Enot\ApiBundle\Provider\ProviderException
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateDictionary($key)
    {
        $countNewItem = 0;
        switch ($key) {
            case 0:
                $result = $this->provider->getDrivers();
                foreach ($result as $item) {
                    if (!$this->getDriverRepository()->findOneByExternalId($item->externalId)) {

                        $driver = new Driver();
                        $driver->setExternalId(ltrim($item->externalId));
                        $driver->setName(ltrim($item->name));
                        $driver->setPhone($this->userManager->getClearPhone($item->phone));

                        $this->entityManager->persist($driver);
                        $countNewItem++;
                    }
                }
                break;
            case  1:
                /** @var VehicleModel[] $result */
                $result = $this->provider->getVehicles();
                foreach ($result as $item) {
                    if (!$this->getVehicleRepository()->findOneByExternalId($item->externalId)) {
                        $vehicle = new Vehicle();
                        $vehicle->setExternalId(ltrim($item->externalId));
                        $vehicle->setName(ltrim($item->name));
                        $vehicle->setDeviceMac(ltrim(strtolower($item->deviceMac)));

                        $this->entityManager->persist($vehicle);
                        $countNewItem++;
                    }
                }
                break;
            case 2:
                $result = $this->provider->getTrailers();
                foreach ($result as $item) {
                    if (!$this->getTrailerRepository()->findOneByExternalId(ltrim($item->externalId))) {
                        $trailer = new Trailer();
                        $trailer->setExternalId(ltrim($item->externalId));
                        $trailer->setName(ltrim($item->name));

                        $this->entityManager->persist($trailer);
                        $countNewItem++;
                    }
                }
                break;
            default:
                $result = null;
                break;
        }

        if ($result && $countNewItem > 0) {
            $this->entityManager->flush();
        }

        return $countNewItem;
    }

    /**
     * @param CreateTransportationRequestModel $request
     * @param Transportation|null $transportation
     * @return string
     * @throws TransportationException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function createTransportation(CreateTransportationRequestModel $request, Transportation $transportation = null)
    {
        if (!$transportation) {
            $transportation = new Transportation();
        }
        if (!$request->driverExternalId && !$this->queryMode) {
            $driver = $this->autoSetterManager->getNearbyVehicleDriver($request->fromAddressGis->latitude . "," . $request->fromAddressGis->longitude);
            if ($driver) {
                $request->driverExternalId = $driver->getDriver()->getExternalId();
            }

        }
        $transportation = $this->fillTransportation($transportation, $request);

        $this->entityManager->persist($transportation);
        $this->entityManager->flush();


        return $transportation;
    }

    /**
     * @param CreateTransportationRequestModel $request
     * @return Transportation|null|object|string
     * @throws TransportationException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateTransportation(CreateTransportationRequestModel $request)
    {
        $transportation = $this->getRepository()->findOneByExternalId($request->transportationExternalId);
        if (!$transportation) {
            throw new TransportationException(EnotError::TRANSPORTATION_NOT_FOUND, '', Response::HTTP_BAD_REQUEST);
        }

        $transportation = $this->createTransportation($request, $transportation);

        return $transportation;
    }

    /**
     * @param Transportation $transportation
     * @return Transportation
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function cancel(Transportation $transportation)
    {
        $transportation->setVehicle(null);
        $transportation->setDriver(null);
        $transportation->setDeletedAt(new \DateTime());

        $this->entityManager->persist($transportation);
        $this->entityManager->flush($transportation);

        return $transportation;
    }

    /**
     * @param Transportation $transportation
     * @param CreateTransportationRequestModel $request
     * @return Transportation
     * @throws TransportationException
     * @throws \Enot\ApiBundle\Provider\ProviderException
     * @throws \Exception
     */
    private function fillTransportation(Transportation $transportation, CreateTransportationRequestModel $request)
    {
        //check unique transportation code
        if (!$transportation->getId()) {
            if ($this->getRepository()->findOneByExternalId($request->transportationExternalId)) {
                throw new TransportationException(EnotError::ENTITY_ALREADY_EXIST, '', Response::HTTP_BAD_REQUEST);
            }
        }

        if ($request->vehicleExternalId) {
            /** @var Vehicle $vehicle */
            $vehicle = $this->getVehicleRepository()->findOneByExternalId($request->vehicleExternalId);
            if (!$vehicle) {
                throw new TransportationException(EnotError::VEHICLE_NOT_FOUND, '', Response::HTTP_BAD_REQUEST);
            }
            $transportation->setVehicle($vehicle);

            if (!$request->driverExternalId && $transportation->getId()) {
                $fieldEvent = new TransportationFieldEvent($transportation, TransportationFieldEvent::VEHICLE);
                $this->dispatcher->dispatch(TransportationFieldEvent::VEHICLE, $fieldEvent);
            }
        }


        if ($request->trailerExternalId) {
            /** @var Trailer $trailer */
            $trailer = $this->getTrailerRepository()->findOneByExternalId($request->trailerExternalId);
            if (!$trailer) {
                throw new TransportationException(EnotError::TRAILER_NOT_FOUND, '', Response::HTTP_BAD_REQUEST);
            }
            $transportation->setTrailer($trailer);

            if (!$request->driverExternalId && $transportation->getId()) {
                $fieldEvent = new TransportationFieldEvent($transportation, TransportationFieldEvent::TRAILER);
                $this->dispatcher->dispatch(TransportationFieldEvent::TRAILER, $fieldEvent);
            }
        }

        if ($request->containerTypeExternalId) {
            /** @var ContainerType $containerType */
            $containerType = $this->getContainerTypeRepository()->findOneByExternalId($request->containerTypeExternalId);
            if (!$containerType) {
                throw new TransportationException(EnotError::CONTAINER_NOT_FOUND, '', Response::HTTP_BAD_REQUEST);
            }
            $transportation->setContainerType($containerType);

            if (!$request->driverExternalId && $transportation->getId()) {
                $fieldEvent = new TransportationFieldEvent($transportation, TransportationFieldEvent::CONTAINER_TYPE);
                $this->dispatcher->dispatch(TransportationFieldEvent::CONTAINER_TYPE, $fieldEvent);
            }
        }

        if ($request->containerNumber) {
            $transportation->setContainerNumber($request->containerNumber);

            if (!$request->driverExternalId && $transportation->getId()) {
                $fieldEvent = new TransportationFieldEvent($transportation, TransportationFieldEvent::CONTAINER_NUMBER);
                $this->dispatcher->dispatch(TransportationFieldEvent::CONTAINER_NUMBER, $fieldEvent);
            }
        }

        if ($request->containerRealSize) {
            $transportation->setContainerRealSize($request->containerRealSize);

            if (!$request->driverExternalId && $transportation->getId()) {
                $fieldEvent = new TransportationFieldEvent($transportation, TransportationFieldEvent::CONTAINER_REAL_SIZE);
                $this->dispatcher->dispatch(TransportationFieldEvent::CONTAINER_REAL_SIZE, $fieldEvent);
            }
        }

        if ($request->price) {
            $transportation->setPrice($request->price);

            if (!$request->driverExternalId && $transportation->getId()) {
                $fieldEvent = new TransportationFieldEvent($transportation, TransportationFieldEvent::CONTAINER_REAL_SIZE);
                $this->dispatcher->dispatch(TransportationFieldEvent::PRICE, $fieldEvent);
            }
        }
        if ($request->estimatedPrice) {
            $transportation->setEstimatedPrice($request->estimatedPrice);

            if (!$request->driverExternalId && $transportation->getId()) {
                $fieldEvent = new TransportationFieldEvent($transportation, TransportationFieldEvent::CONTAINER_REAL_SIZE);
                $this->dispatcher->dispatch(TransportationFieldEvent::PRICE, $fieldEvent);
            }
        }
        if ($request->transportationExternalId) {
            $transportation->setExternalId($request->transportationExternalId);
        }
        if ($request->fromAddress) {
            $transportation->setFromAddress($request->fromAddress);

            if (!$request->driverExternalId && $transportation->getId()) {
                $fieldEvent = new TransportationFieldEvent($transportation, TransportationFieldEvent::FROM_ADDRESS);
                $this->dispatcher->dispatch(TransportationFieldEvent::FROM_ADDRESS, $fieldEvent);
            }
        }
        if ($request->fromAddressGis) {
            $transportation->setFromAddressGis($request->fromAddressGis);

            if (!$request->driverExternalId && $transportation->getId()) {
                $fieldEvent = new TransportationFieldEvent($transportation, TransportationFieldEvent::FROM_ADDRESS_GIS);
                $this->dispatcher->dispatch(TransportationFieldEvent::FROM_ADDRESS_GIS, $fieldEvent);
            }
        }
        if ($request->toAddress) {
            $transportation->setToAddress($request->toAddress);

            if (!$request->driverExternalId && $transportation->getId()) {
                $fieldEvent = new TransportationFieldEvent($transportation, TransportationFieldEvent::TO_ADDRESS);
                $this->dispatcher->dispatch(TransportationFieldEvent::TO_ADDRESS, $fieldEvent);
            }
        }
        if ($request->toAddressGis) {
            $transportation->setToAddressGis($request->toAddressGis);

            if (!$request->driverExternalId && $transportation->getId()) {
                $fieldEvent = new TransportationFieldEvent($transportation, TransportationFieldEvent::TO_ADDRESS_GIS);
                $this->dispatcher->dispatch(TransportationFieldEvent::TO_ADDRESS_GIS, $fieldEvent);
            }
        }

        if ($request->isPassedInvoice) {
            $transportation->setPassedInvoice($request->isPassedInvoice);
        }

        if ($request->deliveryUnladenAddress) {
            $transportation->setDeliveryUnladenAddress($request->deliveryUnladenAddress);

            /** @var Terminal $terminal */
            $terminal = $this->entityManager->getRepository("EnotApiBundle:Terminal")->findOneBy(["address" => $request->deliveryUnladenAddress]);

            if ($terminal) {
                $transportation->setTerminal($terminal);
            }

            if (!$request->driverExternalId && $transportation->getId()) {
                $fieldEvent = new TransportationFieldEvent($transportation, TransportationFieldEvent::DELIVERY_UNLADEN_ADDRESS);
                $this->dispatcher->dispatch(TransportationFieldEvent::DELIVERY_UNLADEN_ADDRESS_GIS, $fieldEvent);
            }
        }
        if ($request->deliveryUnladenAddress) {
            $transportation->setDeliveryUnladenAddress($request->deliveryUnladenAddress);

            if (!$request->driverExternalId && $transportation->getId()) {
                $fieldEvent = new TransportationFieldEvent($transportation, TransportationFieldEvent::DELIVERY_UNLADEN_ADDRESS);
                $this->dispatcher->dispatch(TransportationFieldEvent::DELIVERY_UNLADEN_ADDRESS_GIS, $fieldEvent);
            }
        }
        if ($request->deliveryUnladenAddressGis) {
            $transportation->setDeliveryUnladenAddressGis($request->deliveryUnladenAddressGis);

            if (!$request->driverExternalId && $transportation->getId()) {
                $fieldEvent = new TransportationFieldEvent($transportation, TransportationFieldEvent::DELIVERY_UNLADEN_ADDRESS_GIS);
                $this->dispatcher->dispatch(TransportationFieldEvent::DELIVERY_UNLADEN_ADDRESS_GIS, $fieldEvent);
            }
        }
        if ($request->dateStart) {
            $transportation->setDateStart(new \DateTime($request->dateStart));

            if (!$request->driverExternalId && $transportation->getId()) {
                $fieldEvent = new TransportationFieldEvent($transportation, TransportationFieldEvent::DATE_START);
                $this->dispatcher->dispatch(TransportationFieldEvent::DATE_START, $fieldEvent);
            }
        }
        if ($request->description) {
            $transportation->setDescription($request->description);

            if (!$request->driverExternalId && $transportation->getId()) {
                $fieldEvent = new TransportationFieldEvent($transportation, TransportationFieldEvent::DESCRIPTION);
                $this->dispatcher->dispatch(TransportationFieldEvent::DESCRIPTION, $fieldEvent);
            }
        }

        if ($request->driverExternalId) {
            /** @var Driver $driver */
            $driver = $this->getDriverRepository()->findOneByExternalId($request->driverExternalId);
            if (!$driver) {
                throw new TransportationException(EnotError::DRIVER_NOT_FOUND, '', Response::HTTP_BAD_REQUEST);
            }

            if (!$driver->getCurrentStatus() || ($driver->getCurrentStatus() && $driver->getCurrentStatus()->getEndAt())) {
                throw new TransportationException(EnotError::DRIVER_OFFLINE, '', 406);
            }

            $event = new DriverEvent($transportation, $driver);

            //for event detach
            if ($transportation->getDriver()) {
                $this->dispatcher->dispatch(DriverEvent::DETACHED, $event);
            }

            $transportation->setDriver($driver);
            $transportation->setAssigned(false);
            $transportation->setAssignedAt(null);
            //for event attach
            $this->dispatcher->dispatch(DriverEvent::ATTACHED, $event);
        }

        if (!$request->vehicleExternalId && !$transportation->getVehicle() && $transportation->getDriver()) {
            $status = $this->entityManager->getRepository("EnotApiBundle:AuthorizationVehicleDriver")->findOneBy(['driver' => $transportation->getDriver()]);

            $transportation->setVehicle($status->getVehicle());
        }

        if ($request->driverExternalId && !$this->queryMode) {
            $this->provider->sendDriverNotAssigned($transportation, !$request->driverExternalId && !$transportation->getDriver());
        }

        return $transportation;
    }

    /**
     * @param $transportation
     * @return bool
     * @throws TransportationException
     * @throws \Enot\ApiBundle\Provider\ProviderException
     */
    public function sendAssigned($transportation)
    {
        if ($transportation && !$transportation instanceof Transportation) {
            $transportation = $this->getRepository()->find($transportation);
        }

        if (!$transportation) {
            throw new TransportationException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        $result = $this->provider->sendDriverNotAssigned($transportation, false);
        return $result;
    }


    private function getDuration(?string $point1, ?string $point2)
    {
        $duration = 0;
        if (!$point1 || !$point2) {
            return $duration;
        }

        $url = $this->configurationManager->getYandexApiUrl() . '/route';
        $apiKey = $this->configurationManager->getYandexApiKey();
        $mode = 'driving';
        $weipoints = $point1 . '|' . $point2;

        $requestUrl = $url . '?' .
            'apikey=' . $apiKey . '&' .
            'waypoints=' . $weipoints . '&' .
            'mode=' . $mode;

        try {
            $response = $this->httpClient->get($requestUrl);
            $responseObj = \GuzzleHttp\json_decode($response);

            $legs = $responseObj->route->legs;
            foreach ($legs as $leg) {
                if (strtolower($leg->status) != 'ok') {
                    continue;
                }

                $steps = $leg->steps;
                foreach ($steps as $step) {
                    $duration += $step->duration;
                    $duration -= $step->waiting_duration;
                }
            }
        } catch (\Exception $exception) {
            $duration = 0;
        }

        return ceil($duration);
    }

    /**
     * @param string $deviceMac
     * @param bool $status
     * @param $reason
     * @return string
     * @throws TransportationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Enot\ApiBundle\Provider\ProviderException
     * @throws \Exception
     */
    public function setVehicleStatus(string $deviceMac, bool $status, $reason)
    {
        if (!$deviceMac) {
            throw new TransportationException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        /** @var Vehicle $existVehicle */
        $existVehicle = $this->getVehicleRepository()->findOneByDeviceMac($deviceMac);

        if (!$existVehicle) {
            throw new TransportationException(EnotError::VEHICLE_NOT_FOUND, '', Response::HTTP_BAD_REQUEST);
        }
        $authStatus = $this->entityManager->getRepository("EnotApiBundle:AuthorizationVehicleDriver")->getLastVehicleStatus($existVehicle);
        $authStatus->setEndAt($status ? null : new \DateTime());


        $this->entityManager->persist($authStatus);
        $this->entityManager->flush();

        $result = $this->provider->sendOnLineStatus($existVehicle, $status);

        return $result
            ? ResponseManager::STATUS_SUCCESS
            : ResponseManager::STATUS_FAIL;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function setQueryMode($mode)
    {
        $this->queryMode = $mode;
    }
}
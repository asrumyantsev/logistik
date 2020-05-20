<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Controller;


use Enot\ApiBundle\Model\CreateTransportationRequestModel;
use Enot\ApiBundle\Services\AutoSetterManager;
use Enot\ApiBundle\Services\ResponseManager;
use Enot\ApiBundle\Services\TransportationManager;
use FOS\RestBundle\Controller\Annotations\Post;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;
use Enot\ApiBundle\Entity\Transportation;


class TransportationController extends BaseController
{
    /**
     * @Post("/create", name="transportation_create")
     * @SWG\Tag(name="Deltrans")
     * @SWG\Response(
     *     response=200,
     *     description="Return Transportation object",
     *     @Model(type=Transportation::class)
     * )
     * @param Request $request
     * @return Response
     */
    public function createAction(Request $request)
    {
        try {
            $createRequest = new CreateTransportationRequestModel();
            $createRequest->transportationExternalId = $this->checkRequire($request->request->get('transportation_id'));
            $createRequest->driverExternalId = $request->request->get('driver_id');
            $createRequest->vehicleExternalId = $request->request->get('vehicle_id');
            $createRequest->trailerExternalId = $request->request->get('trailer_id');
            $createRequest->containerTypeExternalId = $request->request->get('container_type_id');
            $createRequest->containerNumber = $request->request->get('container_number');
            $createRequest->containerRealSize = $request->request->get('container_real_size');
            $createRequest->fromAddress = $this->checkRequire($request->request->get('from_address'));
            $createRequest->setFromAddressGis($this->checkRequire($request->request->get('from_address_gis')));
            $createRequest->toAddress = $this->checkRequire($request->request->get('to_address'));
            $createRequest->setToAddressGis($this->checkRequire($request->request->get('to_address_gis')));
            $createRequest->deliveryUnladenAddress = $request->request->get('delivery_unladen_address');
            $createRequest->setDeliveryUnladenAddressGis($request->request->get('delivery_unladen_address_gis'));
            $createRequest->dateStart = $this->checkRequire($request->request->get('date_start'));
            $createRequest->description = $request->request->get('description');
            $createRequest->price = $request->request->get('price');
            $createRequest->estimatedPrice = $request->request->get('estimatedPrice');
            $createRequest->isPassedInvoice = $request->request->get('is_passed_invoice');

            $manager = $this->get('enot_api.services.transportation_manager');

            $result = $manager->createTransportation($createRequest);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        /** @var ResponseManager $responseManager */
        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }

    /**
     * @Post("/update")
     * @SWG\Tag(name="Deltrans")
     * @SWG\Response(
     *     response=200,
     *     description="Return Transportation object",
     *     @Model(type=Transportation::class)
     * )
     * @param Request $request
     * @return Response
     */
    public function updateAction(Request $request)
    {
        try {
            $createRequest = new CreateTransportationRequestModel();
            $createRequest->transportationExternalId = $this->checkRequire($request->request->get('transportation_id'));
            $createRequest->driverExternalId = $request->request->get('driver_id');
            $createRequest->vehicleExternalId = $request->request->get('vehicle_id');
            $createRequest->trailerExternalId = $request->request->get('trailer_id');
            $createRequest->containerTypeExternalId = $request->request->get('container_type_id');
            $createRequest->containerNumber = $request->request->get('container_number');
            $createRequest->containerRealSize = $request->request->get('container_real_size');
            $createRequest->fromAddress = $request->request->get('from_address');
            $createRequest->setFromAddressGis($request->request->get('from_address_gis'));
            $createRequest->toAddress = $request->request->get('to_address');
            $createRequest->setToAddressGis($request->request->get('to_address_gis'));
            $createRequest->deliveryUnladenAddress = $request->request->get('delivery_unladen_address');
            $createRequest->setDeliveryUnladenAddressGis('delivery_unladen_address_gis');
            $createRequest->dateStart = $request->request->get('date_start');
            $createRequest->description = $request->request->get('description');
            $createRequest->price = $request->request->get('price');
            $createRequest->estimatedPrice = $request->request->get('estimatedPrice');
            $createRequest->isPassedInvoice = $request->request->get('is_passed_invoice');

            $manager = $this->get('enot_api.services.transportation_manager');

            $result = $manager->updateTransportation($createRequest);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        /** @var ResponseManager $responseManager */
        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }

    /**
     * @Post("/reactivate")
     * @SWG\Tag(name="Deltrans")
     * @SWG\Response(
     *     response=200,
     *     description="Return Transportation object",
     *     @Model(type=Transportation::class)
     * )
     * @param Request $request
     * @return Response
     */
    public function reactivateAction(Request $request)
    {
        try {
            $transportationExternalId = $this->checkRequire($request->request->get('transportation_id'));
            $manager = $this->get('enot_api.services.transportation_manager');
            $result = $manager->unArchiveTransportation($transportationExternalId);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        /** @var ResponseManager $responseManager */
        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }

    /**
     * @Post("/test")
     * @param Request $request
     * @return Response
     */
    public function testAction(Request $request)
    {
        try {
            /** @var AutoSetterManager $manager */
            $manager = $this->get('enot_api.services.auto_setter_manager');
            $result = $manager->getNearbyVehicleDriver('54.955872,41.376016');
        } catch (\Exception $exception) {
            var_dump($exception->getMessage()); die();
            $result = $exception;
        }

        /** @var ResponseManager $responseManager */
        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }

    /**
     * @Post("/send")
     * @param Request $request
     * @return Response
     */
    public function sendAction(Request $request)
    {
        try {
            $transportationId = $request->request->get("transportation_id");
            /** @var TransportationManager $manager */
            $manager = $this->get('enot_api.services.transportation_manager');
            $result = $manager->sendAssigned($transportationId);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        /** @var ResponseManager $responseManager */
        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }

    /**
     * @Post("/cancel")
     * @param Request $request
     * @return Response
     */
    public function cancelAction(Request $request)
    {
        try {
            $manager = $this->get("enot_api.services.transportation_manager");

            $id = $request->request->get("transportation_id");
            /** @var Transportation $transportation */
            $transportation = $manager->getRepository()->findOneByExternalId($id);
            $result = $manager->cancel($transportation);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        /** @var ResponseManager $responseManager */
        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }
}
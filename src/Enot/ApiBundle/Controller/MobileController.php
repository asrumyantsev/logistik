<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Controller;


use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;
use Enot\ApiBundle\Entity\Transportation;

class MobileController extends BaseController
{
    /**
     * Возвращает список всех не подтвержденных водителем грузоперевозок
     *
     * @Post("/transportation_list")
     * @SWG\Tag(name="Mobile")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="driver_id", type="integer")
     * )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return array of Transportation object",
     *     @Model(type=Transportation::class)
     * )
     * @param Request $request
     * @return Response
     */
    public function getTransportationListAction(Request $request)
    {
        try {
            $driverId = $this->checkRequire($request->request->get('driver_id'));

            $manager = $this->get('enot_api.services.transportation_manager');
            $result = $manager->getAllNotAssignedTransportation($driverId);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }

    /**
     * Возвращает информацию о грузоперевозке по id
     *
     * @Get("/transportation/{id}")
     * @SWG\Tag(name="Mobile")
     * @SWG\Response(
     *     response=200,
     *     description="Return Transportation object",
     *     @Model(type=Transportation::class)
     * )
     * @param $id
     * @return Response
     */
    public function getTransportationInfoAction($id)
    {
        try {
            $transportationId = $this->checkRequire($id);

            $manager = $this->get('enot_api.services.transportation_manager');
            $result = $manager->getRepository()->find($transportationId);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }

    /**
     * Помечает грузоперевозку отмененной по указанной причине
     *
     * @Post("/transportation_cancel")
     * @SWG\Tag(name="Mobile")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="transportation_id", type="integer"),
     *          @SWG\Property(property="reason",type="string"),
     * )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return Transportation object",
     *     @Model(type=Transportation::class)
     * )
     * @param Request $request
     * @return Response
     */
    public function cancelTransportationAction(Request $request)
    {
        try {
            $transportationId = $this->checkRequire($request->request->get('transportation_id'));
            $reason = $this->checkRequire($request->request->get('reason'));

            $manager = $this->get('enot_api.services.transportation_manager');
            $result = $manager->cancelTransportation($transportationId, $reason);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }

    /**
     * Подтверждает выполнение грузоперевозки назначенным водителем
     *
     * @Post("/transportation_assign")
     * @SWG\Tag(name="Mobile")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="driver_id",type="integer"),
     *          @SWG\Property(property="transportation_id", type="integer")
     * )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return Transportation object",
     *     @Model(type=Transportation::class)
     * )
     * @param Request $request
     * @return Response
     */
    public function assignTransportationAction(Request $request)
    {
        try {
            $driverId = $this->checkRequire($request->request->get('driver_id'));
            $transportationId = $this->checkRequire($request->request->get('transportation_id'));

            $manager = $this->get('enot_api.services.transportation_manager');
            $result = $manager->assignTransportation($transportationId, $driverId);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }

    /**
     * Фиксирует точки маршрута транспортировки
     *
     * event_id может быть (2 - прибыл на загрузку, 3 - убыл с загрузки, 4 - прибыл к клиенту,
     * 5 - убыл от клиента, 6 - сдал порожний)
     *
     * @Post("/transportation_point")
     * @SWG\Tag(name="Mobile")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="event_id",type="integer"),
     *          @SWG\Property(property="transportation_id", type="integer"),
     *          @SWG\Property(property="date", type="string")
     *      )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return status of operation"
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function pointTransportationAction(Request $request)
    {
        try {
            $eventId = $this->checkRequire($request->request->get('event_id'));
            $date = $this->checkRequire($request->request->get('date'));
            $transportationId = $this->checkRequire($request->request->get('transportation_id'));
            $user = $this->getUserEntity();

            $manager = $this->get('enot_api.services.transportation_manager');
            $result = $manager->eventTransportation($transportationId, $eventId, $date, $user);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }

    /**
     * Завершает транспортировку
     *
     * @Post("/transportation_finish")
     * @SWG\Tag(name="Mobile")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="transportation_id", type="integer")
     * )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return status of operation"
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function finishTransportationAction(Request $request)
    {
        try {
            $transportationId = $this->checkRequire($request->request->get('transportation_id'));

            $manager = $this->get('enot_api.services.transportation_manager');
            $user = $this->getUserEntity();
            $result = $manager->finishTransportation($transportationId, $user);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }

    /**
     * Устанавливает статус автомобиля (на линии/не на линии)
     *
     * @Post("/vehicle_status")
     * @SWG\Tag(name="Mobile")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="device_mac",type="string", required="true"),
     *          @SWG\Property(property="status", type="boolean", required="true"),
     *          @SWG\Property(property="reason",type="string", required="false")
     * )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return status of operation"
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function vehicleStatusAction(Request $request)
    {
        try {
            $deviceMac = $this->checkRequire($request->request->get('device_mac'));
            $status = $this->checkRequire($request->request->get('status'));
            $reason = $request->request->get('reason');

            $manager = $this->get('enot_api.services.transportation_manager');
            $result = $manager->setVehicleStatus($deviceMac, $status, $reason);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }
}
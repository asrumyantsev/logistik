<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Controller;


use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;
use Enot\ApiBundle\Entity\Vehicle;

class VehicleController extends BaseController
{
    /**
     * @Post("/create", name="vehicle_create")
     * @SWG\Tag(name="Deltrans")
     * @SWG\Response(
     *     response=200,
     *     description="Return Vehicle object",
     *     @Model(type=Vehicle::class)
     * )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        try {
            $name = (string)$this->checkRequire($request->request->get('name'));
            $externalId = (string)$this->checkRequire($request->request->get('id'));
            $deviceMac = (string)$this->checkRequire($request->request->get('device_mac'));
            $overweight = (boolean)$this->checkRequire($request->request->get('overweight'));
            $overweight30 = (boolean)$this->checkRequire($request->request->get('overweight30'));
            $departureToMkad = (boolean)$this->checkRequire($request->request->get('departure_to_mkad'));
            $foots = (string)$this->checkRequire($request->request->get('foots'));
            $partner = (string)$this->checkRequire($request->request->get('external_partner_id'));

            $manager = $this->get('enot_api.services.vehicle_manager');
            $result = $manager->createNewVehicle($name, $externalId, $deviceMac, $overweight, $departureToMkad, $foots, $partner, $overweight30);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }

    /**
     * @Get("/list", name="vehicle_list")
     * @SWG\Tag(name="Deltrans")
     * @SWG\Response(
     *     response=200,
     *     description="Return Driver object",
     *     @Model(type=Vehicle::class)
     * )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request)
    {
        try {
            $result = $this->get('enot_api.services.vehicle_manager')->getRepository()->findBy([
                "deletedAt" => null
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }

    /**
     * @Post("/update",name="vehicle_update")
     * @SWG\Tag(name="Deltrans")
     * @SWG\Response(
     *     response=200,
     *     description="Return Vehicle object",
     *     @Model(type=Vehicle::class)
     * )
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Request $request)
    {
        try {
            $name = (string)$request->request->get('name');
            $externalId = (string)$request->request->get('id');
            $deviceMac = (string)$request->request->get('device_mac');
            $overweight = (boolean)$request->request->get('overweight', false);
            $overweight30 = (boolean)$request->request->get('overweight30', false);
            $departureToMkad = $request->request->get('departure_to_mkad', null);
            $foots = (string)$request->request->get('foots');

            $manager = $this->get('enot_api.services.vehicle_manager');
            $partner = (string)$this->checkRequire($request->request->get('external_partner_id'));

            $result = $manager->updateVehicle($name, $externalId, $deviceMac, $overweight, $overweight30, $departureToMkad, $foots, $partner);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }

    /**
     * @Post("/delete",name="vehicle_delete")
     * @SWG\Tag(name="Deltrans")
     * @SWG\Response(
     *     response=200,
     *     description="Return Vehicle object",
     *     @Model(type=Vehicle::class)
     * )
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request)
    {
        try {
            $externalId = (string)$request->request->get('id');

            $manager = $this->get('enot_api.services.vehicle_manager');

            $result = $manager->deleteVehicle($externalId);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }
}
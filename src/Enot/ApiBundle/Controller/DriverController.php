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
use Enot\ApiBundle\Entity\Driver;


class DriverController extends BaseController
{
    /**
     * @Post("/create", name="driver_create")
     * @SWG\Tag(name="Deltrans")
     * @SWG\Response(
     *     response=200,
     *     description="Return Driver object",
     *     @Model(type=Driver::class)
     * )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        try {
            $name = (string)$this->checkRequire($request->request->get('name'));
            $externalId = (string)$this->checkRequire($request->request->get('id'));
            $phone = (string)$this->checkRequire($request->request->get('phone'));
            $partner = (string)$this->checkRequire($request->request->get('partner'));

            $manager = $this->get('enot_api.services.driver_manager');
            $result = $manager->createNewDriver($name, $externalId, $phone, $partner);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }
    /**
     * @Get("/list", name="driver_list")
     * @SWG\Tag(name="Deltrans")
     * @SWG\Response(
     *     response=200,
     *     description="Return Driver object",
     *     @Model(type=Driver::class)
     * )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request)
    {
        try {
            $result = $this->get('enot_api.services.driver_manager')->getRepository()->findBy([
                "deletedAt" => null
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }

    /**
     * @Post("/update", name="driver_update")
     * @SWG\Tag(name="Deltrans")
     * @SWG\Response(
     *     response=200,
     *     description="Return Driver object",
     *     @Model(type=Driver::class)
     * )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Request $request)
    {
        try {
            $name = (string)$request->request->get('name');
            $externalId = (string)$request->request->get('id');
            $phone = (string)$request->request->get('phone');
            $partner = (string)$request->request->get('partner');

            $manager = $this->get('enot_api.services.driver_manager');
            $result = $manager->updateDriver($name, $externalId, $phone, $partner);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }

    /**
     * @Post("/delete", name="driver_delete")
     * @SWG\Tag(name="Deltrans")
     * @SWG\Response(
     *     response=200,
     *     description="Return Driver object",
     *     @Model(type=Driver::class)
     * )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request)
    {
        try {
            $externalId = (string)$request->request->get('id');

            $manager = $this->get('enot_api.services.driver_manager');
            $result = $manager->deleteDriver($externalId);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }
}
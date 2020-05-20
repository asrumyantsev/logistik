<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019-04-08
 * Time: 10:22
 */

namespace Enot\ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Post;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;
use Enot\ApiBundle\Entity\Partner;

class PartnerController extends BaseController
{
    /**
     * @Post("/create", name="partner_create")
     * @SWG\Tag(name="Deltrans")
     * @SWG\Response(
     *     response=200,
     *     description="Return Partner object",
     *     @Model(type=Partner::class)
     * )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        try {
            $name = (string)$this->checkRequire($request->request->get('name'));
            $inn = (string)$this->checkRequire($request->request->get('inn'));
            $externalId = (string)$this->checkRequire($request->request->get('externalId'));
            $email = (string)$this->checkRequire($request->request->get('email'));
            $phone = (string)$this->checkRequire($request->request->get('phone'));
            $password = (string)$this->checkRequire($request->request->get('password'));

            $manager = $this->get('enot_api.services.partner_manager');
            $result = $manager->create($name, $externalId, $inn, $phone, $email, $password);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }

    /**
     * @Post("/update", name="partner_update")
     * @SWG\Tag(name="Deltrans")
     * @SWG\Response(
     *     response=200,
     *     description="Return Partner object",
     *     @Model(type=Partner::class)
     * )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Request $request)
    {
        try {
            $name = (string)$this->checkRequire($request->request->get('name'));
            $inn = (string)$this->checkRequire($request->request->get('inn'));
            $externalId = (string)$this->checkRequire($request->request->get('externalId'));
            $email = (string)$this->checkRequire($request->request->get('email'));
            $phone = (string)$this->checkRequire($request->request->get('phone'));
            $password = (string)$this->checkRequire($request->request->get('password'));

            $manager = $this->get('enot_api.services.partner_manager');
            $result = $manager->update($name, $externalId, $inn, $phone, $email, $password);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }
}
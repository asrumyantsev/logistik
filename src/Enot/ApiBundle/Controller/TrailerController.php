<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Controller;


use FOS\RestBundle\Controller\Annotations\Post;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;
use Enot\ApiBundle\Entity\Trailer;

class TrailerController extends BaseController
{
    /**
     * @Post("/create", name="trailer_create")
     * @SWG\Tag(name="Deltrans")
     * @SWG\Response(
     *     response=200,
     *     description="Return Trailer object",
     *     @Model(type=Trailer::class)
     * )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        try {
            $name = (string)$this->checkRequire($request->request->get('name'));
            $externalId = (string)$this->checkRequire($request->request->get('id'));

            $manager = $this->get('enot_api.services.trailer_manager');
            $result = $manager->createNewTrailer($name, $externalId);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }
}
<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Controller;


use Enot\ApiBundle\Model\AuthStatusModel;
use FOS\RestBundle\Controller\Annotations\Post;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

class AuthController extends BaseController
{
    /**
     * Проверяет пару логин/пароль
     *
     * Response
     * <pre>
     * {
     *      "request_id": "48330269-B040-440A-883B-ECB1D28675C3",
     *      "status_code": 200,
     *      "data": "success",
     *      "error": null
     * }
     * </pre>
     * @Post("/check")
     * @param Request $request
     * @SWG\Tag(name="Auth")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="phone",
     *              description="Phone number for registration",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="password",
     *              description="user password",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="mac",
     *              description="Device Wi-fi mac address",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="position",
     *              description="Device position in format like latitude,longitude",
     *              type="string"
     *          )
     *     )
     * )
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="Return status of operation or Driver details",
     *     @Model(type=AuthStatusModel::class)
     * )
     * @return Response
     */
    public function checkAuthAction(Request $request)
    {
        try {
            $phone = $request->request->get('phone');
            $password = $request->request->get('password');
            $mac = $request->request->get('mac');
            $position = $request->request->get('position');

            $userManager = $this->get('enot_api.user_manager');
            $result = $userManager->checkAuth($phone, $password, $mac, $position);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result, true, ['Mobile']);
        return $response;
    }

    /**
     * Обновляет координаты водителя
     *
     * Response
     * <pre>
     * {
     *      "request_id": "48330269-B040-440A-883B-ECB1D28675C3",
     *      "status_code": 200,
     *      "data": "success",
     *      "error": null
     * }
     * </pre>
     * @Post("/update")
     * @param Request $request
     * @SWG\Tag(name="Auth")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="position",
     *              description="Device position in format like latitude,longitude",
     *              type="string"
     *          )
     *     )
     * )
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="Return status of operation or Driver details",
     *     @Model(type=AuthStatusModel::class)
     * )
     * @return Response
     */
    public function updatePosAction(Request $request)
    {
        try {
            $phone = $request->request->get('phone');
            $position = $request->request->get('position');
            $userManager = $this->get('enot_api.user_manager');
            $result = $userManager->updatePosition($phone, $position);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result, true, ['Mobile']);
        return $response;
    }
}
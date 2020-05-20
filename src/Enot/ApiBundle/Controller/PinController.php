<?php

namespace Enot\ApiBundle\Controller;


use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;

class PinController extends BaseController
{
    /**
     * Отправляет код подтверждения на номер телефона клиента. Если пользователя в системе еще нет - создает нового пользователя
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
     *
     * @Post("/")
     * @param Request $request
     * @SWG\Tag(name="Pin code")
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
     *          )
     *     )
     * )
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="Return status of operation"
     * )
     * @return Response
     */
    public function pinAction(Request $request)
    {
        try {
            $phone = $request->request->get('phone');
            $password = $request->request->get('password');

            $userManager = $this->get('enot_api.user_manager');
            $isSmsSend = $this->getParameter('is_sms_send');
            $result = $userManager->sendPinCode($phone, $password, $isSmsSend);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }

    /**
     * Подтверждает введенный пин-код
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
     *
     * @Post("/confirm")
     * @param Request $request
     * @SWG\Tag(name="Pin code")
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
     *              property="code",
     *              description="Code confirmation",
     *              type="integer"
     *          )
     *     )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return status of operation"
     * )
     * @return Response
     */
    public function pinConfirmationAction(Request $request)
    {
        try {
            $phone = $request->request->get('phone');
            $code = $request->request->get('code');

            $userManager = $this->get('enot_api.user_manager');
            $result = $userManager->confirmUser($phone, $code);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        $response = $this->get("enot_api.response_manager")->getResponse($result);
        return $response;
    }
}
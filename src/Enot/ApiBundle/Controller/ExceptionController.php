<?php

namespace Enot\ApiBundle\Controller;

use Enot\ApiBundle\Services\Main\MasterException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionController extends Controller
{
    /**
     * Converts an Exception to a Response.
     *
     * @param Exception|\Throwable $exception
     *
     * @throws \InvalidArgumentException
     *
     * @return Response
     */
    public function showAction($exception)
    {
        if ($exception instanceof HttpException) {
            $code = $exception->getStatusCode();
        } else {
            $code = $exception->getCode();
        }

        if ($code === 0 || $code === 1) {
            $code = 500;
        }

        if ($exception instanceof MasterException) {
            $responseException = new MasterException($exception->getErrorId(), $exception->getMessage(), $code);
        } else {
            $responseException = new Exception($exception->getMessage(), $code);
        }
        $response = $this->get("enot_api.response_manager")->getResponse($responseException);

        return $response;
    }
}

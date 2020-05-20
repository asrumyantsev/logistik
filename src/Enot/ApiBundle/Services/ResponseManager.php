<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Services;


use Doctrine\ORM\EntityManager;
use Enot\ApiBundle\Entity\Error;
use Enot\ApiBundle\Model\ErrorResponseModel;
use Enot\ApiBundle\Model\ResponseModel;
use Enot\ApiBundle\Services\Main\MasterException;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;


class ResponseManager
{
    const
        STATUS_SUCCESS = 'success',
        STATUS_FAIL = 'fail';

    /** @var EntityManager */
    private $entityManager;

    /** @var ViewHandlerInterface */
    private $viewHandler;

    /** @var Serializer */
    private $serializer;

    public function __construct(EntityManager $entityManager, ViewHandlerInterface $viewHandler, Serializer $serializer)
    {
        $this->entityManager = $entityManager;
        $this->viewHandler = $viewHandler;
        $this->serializer = $serializer;
    }

    /**
     * Возвращает модель ответа
     *
     * @param $data
     * @param bool $needSerializedContext
     * @param array $serializeGroups
     * @return Response
     */
    public function getResponse($data, $needSerializedContext = false, $serializeGroups = ['List'])
    {
        if ($data instanceof \Exception) {
            $response = $this->getErrorResponse($data);
            $response->setStatusCode($data->getCode());
        } else {
            $response = new ResponseModel();
            $response->setStatusCode(Response::HTTP_OK);
            if ($needSerializedContext) {
                $context = new SerializationContext();
                $context->setSerializeNull(true);
                $context->setGroups($serializeGroups);
                $data = $this->serializer->toArray($data, $context);
            }
            $response->setData($data);
        }
        $view = View::create($response, $response->getStatusCode());
        return $this->viewHandler->handle($view);
    }

    /**
     * Заполняет модель ответа с параметрами из исключения
     *
     * @param \Exception|MasterException $exception
     * @return ResponseModel
     */
    private function getErrorResponse($exception)
    {
        $response = new ResponseModel();

        if ($exception instanceof HttpException) {
            $code = $exception->getStatusCode();
        } else {
            $code = $exception->getCode();
        }

        if ($code === 0 || $code === 1) {
            $code = 500;
        }

        $response->setStatusCode($code);
        $errorResponse = new ErrorResponseModel();

        if ($exception instanceof MasterException) {
            /** @var Error $error */
            $error = $this->getError($exception);
            if (isset($error) && $error) {
                $errorResponse->setCode($error->getId());
                $errorResponse->setMessage($error->getEn());
                $errorResponse->setMessageRu($error->getRu());
            } else {
                $errorResponse->setMessage($exception->getMessage());
                $errorResponse->setMessageRu($exception->getMessage());
            }
        } else {
            $errorResponse->setMessage($exception->getMessage());
            $errorResponse->setMessageRu($exception->getMessage());
        }

        $response->setError($errorResponse);

        return $response;
    }

    /**
     * @param MasterException $exception
     * @return \Enot\ApiBundle\Entity\Error|null
     */
    private function getError($exception)
    {
        $error = null;
        $errorId = $exception->getErrorId();
        if (isset($errorId) && $errorId > 0) {
            $errorsRepository = $this->entityManager->getRepository('EnotApiBundle:Error');
            /** @var Error $error */
            $error = $errorsRepository->find($errorId);
        }

        return $error;
    }
}
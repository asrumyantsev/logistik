<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Model;


class ResponseModel
{
    /**
     * @var string
     */
    private $requestId;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var ErrorResponseModel
     */
    private $error;

    /**
     * @return string
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @param string $requestId
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return ErrorResponseModel
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param ErrorResponseModel $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}
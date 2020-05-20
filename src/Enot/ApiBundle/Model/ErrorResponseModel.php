<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Model;


class ErrorResponseModel
{
    /**
     * @var int
     */
    private $code;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $messageRu;

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessageRu()
    {
        return $this->messageRu;
    }

    /**
     * @param string $messageRu
     */
    public function setMessageRu($messageRu)
    {
        $this->messageRu = $messageRu;
    }
}
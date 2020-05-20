<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Services\Main;


use Throwable;

class MasterException extends \Exception
{
    /**
     * @var int
     */
    private $_errorId;

    public function __construct($errorId, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->_errorId = $errorId;
    }

    /**
     * @return int
     */
    public function getErrorId()
    {
        return $this->_errorId;
    }

    /**
     * @param int $_errorId
     */
    public function setErrorId($_errorId)
    {
        $this->_errorId = $_errorId;
    }
}
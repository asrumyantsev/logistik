<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Event;


use Enot\ApiBundle\Entity\Driver;
use Enot\ApiBundle\Entity\Transportation;
use Symfony\Component\EventDispatcher\Event;

class DriverEvent extends Event
{
    const ATTACHED = 'driver.attached';
    const DETACHED = 'driver.detached';

    /**
     * @var Transportation
     */
    private $transportation;

    /**
     * @var Driver
     */
    private $driver;

    public function __construct(Transportation $transportation, Driver $driver)
    {
        $this->transportation = $transportation;
        $this->driver = $driver;
    }

    /**
     * @return Transportation
     */
    public function getTransportation()
    {
        return $this->transportation;
    }

    /**
     * @return Driver
     */
    public function getDriver()
    {
        return $this->driver;
    }


}
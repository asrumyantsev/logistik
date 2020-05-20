<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Model;


use Enot\ApiBundle\Entity\Driver;
use Enot\ApiBundle\Entity\Vehicle;
use JMS\Serializer\Annotation as Serializer;

class AuthStatusModel
{
    /**
     * @var Driver $driver
     * @Serializer\Groups({"Mobile"})
     */
    public $driver;

    /**
     * @var Vehicle $vehicle
     * @Serializer\Groups({"Mobile"})
     */
    public $vehicle;

    /**
     * AuthStatusModel constructor.
     * @param Driver $driver
     * @param Vehicle $vehicle
     */
    public function __construct(Driver $driver, Vehicle $vehicle)
    {
        $this->driver = $driver;
        $this->vehicle = $vehicle;
    }
}
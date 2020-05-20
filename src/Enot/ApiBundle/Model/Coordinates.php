<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Model;


class Coordinates
{
    public function __construct($latitude, $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * @var string
     */
    public $longitude;

    /**
     * @var string
     */
    public $latitude;
}
<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Provider;


interface ProviderInterface
{
    public function getDrivers();

    public function getVehicles();

    public function getTrailers();
}
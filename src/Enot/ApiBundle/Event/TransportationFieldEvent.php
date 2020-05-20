<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Event;


use Enot\ApiBundle\Entity\Transportation;
use Symfony\Component\EventDispatcher\Event;

class TransportationFieldEvent extends Event
{
    const VEHICLE = 'field.vehicle';
    const TRAILER = 'field.trailer';
    const CONTAINER_TYPE = 'field.container_type';
    const CONTAINER_NUMBER = 'field.container_number';
    const CONTAINER_REAL_SIZE = 'field.container_real_size';
    const PRICE = 'field.price';
    const ESTIMATED_PRICE = 'field.estimated_price';
    const FROM_ADDRESS = 'field.from_address';
    const FROM_ADDRESS_GIS = 'field.from_address_gis';
    const TO_ADDRESS = 'field.to_address';
    const TO_ADDRESS_GIS = 'field.to_address_gis';
    const DELIVERY_UNLADEN_ADDRESS = 'field.delivery_unladen_address';
    const DELIVERY_UNLADEN_ADDRESS_GIS = 'field.delivery_unladen_address_gis';
    const DATE_START = 'field.date_start';
    const DESCRIPTION = 'field.description';

    /**
     * @var Transportation
     */
    private $transportation;
    private $field;

    public function __construct(Transportation $transportation, string $field)
    {
        $this->transportation = $transportation;
        $this->field = $field;
    }

    /**
     * @return Transportation
     */
    public function getTransportation()
    {
        return $this->transportation;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }
}
<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Model;


class CreateTransportationRequestModel
{
    public $transportationExternalId;
    public $driverExternalId;
    public $vehicleExternalId;
    public $trailerExternalId;
    public $containerNumber;
    public $containerRealSize;
    public $containerTypeExternalId;
    public $fromAddress;
    /** @var Coordinates */
    public $fromAddressGis;
    public $toAddress;
    /** @var Coordinates */
    public $toAddressGis;
    public $deliveryUnladenAddress;
    /** @var Coordinates */
    public $deliveryUnladenAddressGis;
    public $dateStart;
    public $description;
    public $price;
    public $estimatedPrice;
    public $isPassedInvoice;

    /**
     * @param string $fromAddressGis
     */
    public function setFromAddressGis($fromAddressGis): void
    {
        $this->fromAddressGis = $this->parseCoordinates($fromAddressGis);
    }

    /**
     * @param string $toAddressGis
     */
    public function setToAddressGis($toAddressGis): void
    {
        $this->toAddressGis = $this->parseCoordinates($toAddressGis);
    }

    /**
     * @param string $deliveryUnladenAddressGis
     */
    public function setDeliveryUnladenAddressGis($deliveryUnladenAddressGis): void
    {
        $this->deliveryUnladenAddressGis = $this->parseCoordinates($deliveryUnladenAddressGis);
    }

    /**
     * @param string $coordinatesString
     * @return Coordinates
     */
    private function parseCoordinates($coordinatesString)
    {
        if (!$coordinatesString) {
            $coordinatesString = '';
        }

        $coordinatesArray = explode(',', str_replace(' ', '', $coordinatesString));
        if (!$coordinatesArray || count($coordinatesArray) < 2) {
            return null;
        }

        $latitude = $coordinatesArray[0];
        $longitude = $coordinatesArray[1];

        return new Coordinates($latitude, $longitude);
    }

}
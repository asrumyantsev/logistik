<?php


namespace Enot\ApiBundle\Services;


use Enot\ApiBundle\Entity\AuthorizationVehicleDriver;
use Enot\ApiBundle\Services\Main\HttpClientInterface;

class AutoSetterManager
{
    /**
     * @var DriverManager
     */
    private $driverManager;

    /**
     * @var VehicleManager
     */
    private $vehicleManager;

    /**
     * @var ConfigurationManager
     */
    private $configurationManager;
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    public function __construct(DriverManager $driverManager,
                                VehicleManager $vehicleManager,
                                ConfigurationManager $configurationManager,
                                HttpClientInterface $httpClient)
    {
        $this->driverManager = $driverManager;
        $this->vehicleManager = $vehicleManager;
        $this->configurationManager = $configurationManager;
        $this->httpClient = $httpClient;
    }

    /**
     * @param string $destinationPoint
     * @return AuthorizationVehicleDriver
     * @throws \Exception
     */
    public function getNearbyVehicleDriver(string $destinationPoint)
    {
        $authVehicleDrivers = $this->vehicleManager->getAuthVehicleDriverRepository()->findAllFree();

        $vehiclePositions = [];
        $drivers = [];
        foreach ($authVehicleDrivers as $authVehicleDriver) {
            $position = $authVehicleDriver->getPosition();
            if ($position) {
                $drivers[] = $authVehicleDriver;
                $vehiclePositions[] = $position['latitude'] . ',' . $position['longitude'];
            }
        }
        $vehiclePositions = substr(implode('|', $vehiclePositions), 0, -1);
        return $this->findNearbyByDistanceMatrix($destinationPoint, $vehiclePositions, $drivers);
    }

    /**
     * @param string $destinationPoint
     * @param string $vehiclePositions
     * @param $drivers AuthorizationVehicleDriver[]
     * @return AuthorizationVehicleDriver
     */
    private function findNearbyByDistanceMatrix(string $destinationPoint, string $vehiclePositions, $drivers)
    {
        if (!$destinationPoint || !$vehiclePositions) {
            return null;
        }

        $url = $this->configurationManager->getYandexApiUrl() . '/distancematrix';
        $apiKey = $this->configurationManager->getYandexApiKey();

        $result = null;
        try {
            $requestUrl = $url . '?' .
                'origins=' . $vehiclePositions . '&' .
                'destinations=' . $destinationPoint . '&' .
                'mode=driving' . '&' .
                'apikey=' . $apiKey . '';
            $response = $this->httpClient->get($requestUrl);
            $responseObj = \GuzzleHttp\json_decode($response);
            $currentDuration = 0;

            reset($drivers);
            foreach ($responseObj->rows as $elements) {
                $driver = current($drivers);
                $duration = 0;
                foreach ($elements->elements as $element) {
                    if ($element->status != 'ok') {
                        continue;
                    }

                    $duration += $element->duration->value;
                }

                if ($duration < $currentDuration || !$currentDuration) {
                    $currentDuration = $duration;
                    $result = $driver;
                }
                next($drivers);
            }

        } catch (\Exception $exception) {
            $result = null;
        }
        return $result;
    }
}
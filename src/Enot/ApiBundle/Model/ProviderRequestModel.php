<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Model;


use Enot\ApiBundle\Services\Main\HttpClientInterface;
use Enot\LogBundle\Utils\Logger;

class ProviderRequestModel
{
    /**
     * @var string
     */
    public $connectionString;

    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var HttpClientInterface
     */
    public $httpClient;
}
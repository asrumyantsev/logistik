<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Enot\ApiBundle\Entity\Configuration;

class ConfigurationRepository extends EntityRepository
{
    const KEY_CONNECTION_STRING = 'connection_string';
    const KEY_YANDEX_APIKEY = 'yandex_apikey';
    const KEY_YANDEX_API_URL = 'yandex_api_url';

    /**
     * @return null|string
     */
    public function findConnectionString()
    {
        /** @var Configuration $configItem */
        $configItem = $this->find(self::KEY_CONNECTION_STRING);
        $result = null;
        if ($configItem) {
            $result = $configItem->getValue();
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function findYandexApiKey()
    {
        /** @var Configuration $configItem */
        $configItem = $this->find(self::KEY_YANDEX_APIKEY);
        $result = null;
        if ($configItem) {
            $result = $configItem->getValue();
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function findYandexApiUrl()
    {
        /** @var Configuration $configItem */
        $configItem = $this->find(self::KEY_YANDEX_API_URL);
        $result = null;
        if ($configItem) {
            $result = $configItem->getValue();
        }

        return $result;
    }
}
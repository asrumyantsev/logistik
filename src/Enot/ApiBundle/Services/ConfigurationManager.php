<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Services;


use Doctrine\ORM\EntityManager;

class ConfigurationManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return \Enot\ApiBundle\Repository\ConfigurationRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository('EnotApiBundle:Configuration');
    }

    /**
     * @return null|string
     */
    public function getConnectionString()
    {
        return $this->getRepository()->findConnectionString();
    }

    /**
     * @return null|string
     */
    public function getYandexApiKey()
    {
        return $this->getRepository()->findYandexApiKey();
    }

    /**
     * @return null|string
     */
    public function getYandexApiUrl()
    {
        return $this->getRepository()->findYandexApiUrl();
    }
}
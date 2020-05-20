<?php
/**
 * ...
 */

namespace Enot\LogBundle\Utils;

use Doctrine\ODM\MongoDB\DocumentManager;
use Enot\ApiBundle\Document\Log;
use Enot\ApiBundle\Document\LogOCPP;
use Enot\ApiBundle\Document\LogSuppliers;
use Enot\ApiBundle\Entity\User;
use Enot\LogBundle\Exceptions\LoggerException;
use Enot\ApiBundle\Utils\EnotError;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;

class Logger
{
    /** @var Log */
    private $logEntity;

    /** @var DocumentManager */
    private $documentManager;

    /** @var User */
    private $user;

    private $requestId = null;

    const
        CATEGORY_REQUEST = 'request',
        CATEGORY_RESPONSE = 'response';

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * @param User $user
     * @param string $requestId
     */
    public function setParameters(?User $user, ?string $requestId)
    {
        $this->requestId = $requestId ?? $this->requestId;
        $this->user = $user ?? $this->user;
    }

    /**
     * @param array $context
     * @param null|string $event
     * @param null|string $domain
     * @param null|string $category
     * @throws LoggerException
     */
    public function log(array $context = [], ?string $event, ?string $domain, ?string $category = null)
    {
        $this->logEntity = new Log();
        $this->writeLog($context, $event, $domain, $category);
    }

    /**
     * @param array $context
     * @param null|string $event
     * @param null|string $domain
     * @param null|string $category
     * @throws LoggerException
     */
    public function logSupplier(array $context = [], ?string $event, ?string $domain, ?string $category = null)
    {
        $this->logEntity = new LogSuppliers();
        $this->writeLog($context, $event, $domain, $category);
    }

    /**
     * @param array $context
     * @param null|string $event
     * @param null|string $domain
     * @param null|string $category
     * @throws LoggerException
     */
    private function writeLog(array $context = [], ?string $event, ?string $domain, ?string $category = null)
    {
        try {
            $this->fillLog($context, $event, $domain, $category, $this->requestId);

            $this->documentManager->persist($this->logEntity);
            $this->documentManager->flush();
        } catch (Exception $exception) {
            throw new LoggerException(EnotError::ERR_LOG, '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param array $context
     * @param $event
     * @param $domain
     * @param null $category
     * @param null $requestId
     */
    private function fillLog(array $context = [], $event, $domain, $category = null, $requestId = null)
    {
        $context = [
            'user' => (isset($this->user) && $this->user instanceof User) ? $this->user->getId() : $this->user,
            'data' => $context
        ];

        $domain = $this->getName($domain);
        $this->logEntity->setDomain($domain);
        $this->logEntity->setEvent($event);

        if (isset($category)) {
            $this->logEntity->setCategory($category);
        }
        if (isset($requestId)) {
            $this->logEntity->setRequestId($requestId);
        }
        $this->logEntity->setContext($context);
    }

    /**
     * @param $classPath
     * @return string
     */
    private function getName($classPath)
    {
        $path = explode('\\', $classPath);
        return array_pop($path);
    }

    /**
     * Generates new request id
     *
     * @return string
     */
    public static function getGuid()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
}
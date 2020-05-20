<?php

namespace Enot\ApiBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * Class Log
 * @package Enot\ApiBundle\Document
 * @MongoDB\Document(collection="log")
 * @MongoDB\HasLifecycleCallbacks()
 */
class Log
{

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\Field(name="request_id", type="string")
     */
    private $requestId;

    /**
     * @MongoDB\Field(type="string")
     */
    private $domain;

    /**
     * @MongoDB\Field(type="string")
     */
    private $event;

    /**
     * @MongoDB\Field(type="string")
     */
    private $category;

    /**
     * @MongoDB\Field(type="hash")
     * @var array
     */
    private $context;

    /**
     * @MongoDB\Field(type="timestamp")
     */
    private $date;

    /**
     * @MongoDB\PrePersist()
     */
    public function onPrePersist()
    {
        $date = new \DateTime('now');
        $this->date = $date->getTimestamp();
    }

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set requestId
     *
     * @param string $requestId
     * @return $this
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
        return $this;
    }

    /**
     * Get requestId
     *
     * @return string $requestId
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * Set domain
     *
     * @param string $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * Get domain
     *
     * @return string $domain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set event
     *
     * @param string $event
     * @return $this
     */
    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * Get event
     *
     * @return string $event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set category
     *
     * @param string $category
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Get category
     *
     * @return string $category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set context
     *
     * @param array $context
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Get context
     *
     * @return array $context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Get date
     *
     * @return \DateTime $dateTime
     */
    public function getDate()
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->date);
        return $dateTime;
    }
}

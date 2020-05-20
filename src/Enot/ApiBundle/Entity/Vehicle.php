<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Vehicle
 * @package Enot\ApiBundle\Entity
 *
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="vehicles", indexes={
 *     @ORM\Index(name="driver_id", columns={"device_mac", "deleted_at"})
 *     })
 * @ORM\Entity(repositoryClass="Enot\ApiBundle\Repository\VehicleRepository")
 */
class Vehicle
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @Serializer\Groups({"Mobile"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @Serializer\Groups({"Mobile"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="external_id", type="string", length=255, nullable=false)
     */
    private $externalId;

    /**
     * @var OverweightType
     *
     * @Serializer\Groups({"Default"})
     * @ORM\ManyToOne(targetEntity="Enot\ApiBundle\Entity\OverweightType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="overweight_type_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $overweight;

    /**
     * @var boolean
     *
     * @ORM\Column(name="departure_to_mkad", type="boolean", nullable=false)
     */
    private $departureToMkad;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_passed", type="boolean", nullable=false)
     */
    private $passed = false;

    /**
     * @var string
     *
     * @ORM\Column(name="foots", type="string", length=255, nullable=false)
     * @Serializer\Groups({"Mobile"})
     */
    private $foots;

    /**
     * @var string
     *
     * @ORM\Column(name="device_mac", type="string", length=255, nullable=true)
     * @Serializer\Groups({"Mobile"})
     */
    private $deviceMac;

    /**
     * @var Partner
     *
     * @Serializer\Groups({"Default"})
     * @ORM\ManyToOne(targetEntity="Enot\ApiBundle\Entity\Partner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="partner_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $partner;

    /**
     * @Serializer\Exclude()
     * @ORM\OneToMany(targetEntity="Enot\ApiBundle\Entity\Transportation", mappedBy="vehicle")
     */
    private $transportations;

    /**
     * @Serializer\Exclude()
     * @ORM\OneToMany(targetEntity="Enot\ApiBundle\Entity\AuthorizationVehicleDriver", mappedBy="vehicle")
     */
    private $statuses;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Vehicle
     */
    public function setId(int $id): Vehicle
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Vehicle
     */
    public function setName(string $name): Vehicle
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getExternalId(): string
    {
        return $this->externalId;
    }

    /**
     * @param string $externalId
     * @return Vehicle
     */
    public function setExternalId(string $externalId): Vehicle
    {
        $this->externalId = $externalId;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeviceMac(): string
    {
        return $this->deviceMac;
    }

    /**
     * @param string $deviceMac
     * @return Vehicle
     */
    public function setDeviceMac(string $deviceMac): Vehicle
    {
        $this->deviceMac = strtolower($deviceMac);
        return $this;
    }

    /**
     * @return OverweightType
     */
    public function getOverweight()
    {
        return $this->overweight;
    }

    /**
     * @param OverweightType $overweight
     * @return Vehicle
     */
    public function setOverweight($overweight)
    {
        $this->overweight = $overweight;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDepartureToMkad()
    {
        return $this->departureToMkad;
    }

    /**
     * @param boolean $departureToMkad
     * @return Vehicle
     */
    public function setDepartureToMkad($departureToMkad)
    {
        $this->departureToMkad = $departureToMkad;
        return $this;
    }

    /**
     * @return string
     */
    public function getFoots()
    {
        return $this->foots;
    }

    /**
     * @param string $foots
     * @return Vehicle
     */
    public function setFoots($foots)
    {
        $this->foots = $foots;
        return $this;
    }

    /**
     * @return Partner
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * @param Partner $partner
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
    }

    /**
     * @return PersistentCollection
     */
    public function getTransportations()
    {
        return $this->transportations;
    }

    /**
     * @param mixed $transportations
     * @return Vehicle
     */
    public function setTransportations($transportations)
    {
        $this->transportations = $transportations;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTime $deletedAt
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }

    /**
     * @return mixed
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    /**
     * @param mixed $statuses
     * @return Vehicle
     */
    public function setStatuses($statuses)
    {
        $this->statuses = $statuses;
        return $this;
    }

    public function getStatus()
    {
        /** @var PersistentCollection $collection */
        $collection = $this->getTransportations();
        $array = $collection->toArray();

        $last = end($array);
        return $last ? $last->getLastEvent() : 0;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function isAuth()
    {
        return $this->getCurrentStatus() ? true : false;
    }

    /**
     * @return bool
     */
    public function isPassed(): bool
    {
        return $this->passed;
    }

    /**
     * @param bool $passed
     */
    public function setPassed(bool $passed): void
    {
        $this->passed = $passed;
    }

    /**
     * @return AuthorizationVehicleDriver
     */
    public function getCurrentStatus()
    {
        /** @var PersistentCollection $statuses */
        $statuses = $this->statuses;
        return !$statuses->isEmpty() ? $statuses->last() : null;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist()
     * @throws \Exception
     */
    public function setUpdatedAt()
    {
        $this->updatedAt = new \DateTime();
    }
}
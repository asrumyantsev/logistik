<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * Class Driver
 * @package Enot\ApiBundle\Entity
 *
 * @ORM\Table(name="drivers")
 * @ORM\Entity(repositoryClass="Enot\ApiBundle\Repository\DriverRepository")
 * @Serializer\ExclusionPolicy("none")
 */
class Driver
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private $id;

    /**
     * @var string
     *
     * @Serializer\Groups({"List","Mobile"})
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="external_id", type="string", length=255, nullable=false, unique=true)
     */
    private $externalId;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=20, nullable=false, unique=false)
     */
    private $phone;

    /**
     * @Serializer\Exclude()
     * @ORM\OneToMany(targetEntity="Enot\ApiBundle\Entity\Transportation", mappedBy="driver")
     */
    private $transportations;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @Serializer\Groups({"List","Mobile"})
     * @ORM\OneToMany(targetEntity="Enot\ApiBundle\Entity\AuthorizationVehicleDriver", mappedBy="driver")
     */
    private $statuses;

    /**
     * @Serializer\Groups({"List","Mobile"})
     * @var int
     */
    private $completedTransportationCount = 0;

    /**
     * @Serializer\VirtualProperty("driver_id")
     * @Serializer\Groups({"Mobile"})
     */
    public function getDriverId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return Driver
     */
    public function setPhone(string $phone): Driver
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Driver
     */
    public function setId(int $id): Driver
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
     * @return Driver
     */
    public function setName(string $name): Driver
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
     * @return Driver
     */
    public function setExternalId(string $externalId): Driver
    {
        $this->externalId = $externalId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTransportations()
    {
        return $this->transportations;
    }

    /**
     * @param mixed $transportations
     * @return Driver
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
     * @return Transportation|null
     */
    public function getActiveSession()
    {

        /** @var Transportation $transportation */
        foreach ($this->getTransportations() as $transportation) {
            if ($transportation->getLastEvent() != Event::FINISH) {
                return $transportation;
            }
        }
        return null;
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
     * @return Vehicle
     */
    public function getVehicle()
    {
        return $this->getCurrentStatus() ? $this->getCurrentStatus()->getVehicle() : null;
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
     */
    public function setStatuses($statuses): void
    {
        $this->statuses = $statuses;
    }

    /**
     * @return int
     */
    public function getCompletedTransportationCount(): int
    {
        return $this->completedTransportationCount;
    }

    /**
     * @param int $completedTransportationCount
     */
    public function setCompletedTransportationCount(int $completedTransportationCount): void
    {
        $this->completedTransportationCount = $completedTransportationCount;
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
}
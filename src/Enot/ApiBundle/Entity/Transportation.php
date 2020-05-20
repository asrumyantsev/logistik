<?php

namespace Enot\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use JMS\Serializer\Annotation as Serializer;

use Enot\ApiBundle\Model\Coordinates;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Transportation
 * @package Enot\ApiBundle\Entity
 *
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="transportations", indexes={
 *     @ORM\Index(name="driver_id", columns={"driver_id"}),
 *     @ORM\Index(name="vehicle_id", columns={"vehicle_id"}),
 *     @ORM\Index(name="trailer_id", columns={"trailer_id"})
 *     })
 * @ORM\Entity(repositoryClass="Enot\ApiBundle\Repository\TransportationRepository")
 * @UniqueEntity("externalId")
 */
class Transportation
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
     * @ORM\Column(name="external_id", type="string", length=255, nullable=false, unique=true)
     */
    private $externalId;

    /**
     * @var Driver
     *
     * @ORM\ManyToOne(targetEntity="Enot\ApiBundle\Entity\Driver")
     * @ORM\JoinColumns(
     *     @ORM\JoinColumn(name="driver_id", referencedColumnName="id", nullable=true)
     * )
     */
    private $driver;

    /**
     * @var Vehicle
     *
     * @ORM\ManyToOne(targetEntity="Enot\ApiBundle\Entity\Vehicle")
     * @ORM\JoinColumns(
     *     @ORM\JoinColumn(name="vehicle_id", referencedColumnName="id", nullable=true)
     * )
     */
    private $vehicle;

    /**
     * @var Trailer
     *
     * @ORM\ManyToOne(targetEntity="Enot\ApiBundle\Entity\Trailer")
     * @ORM\JoinColumns(
     *     @ORM\JoinColumn(name="trailer_id", referencedColumnName="id", nullable=true)
     * )
     */
    private $trailer;

    /**
     * @var ContainerType
     *
     * @ORM\ManyToOne(targetEntity="Enot\ApiBundle\Entity\ContainerType")
     * @ORM\JoinColumns(
     *     @ORM\JoinColumn(name="container_type_id", referencedColumnName="id", nullable=true)
     * )
     */
    private $containerType;

    /**
     * @var Terminal
     *
     * @ORM\ManyToOne(targetEntity="Enot\ApiBundle\Entity\Terminal")
     * @ORM\JoinColumns(
     *     @ORM\JoinColumn(name="terminal_id", referencedColumnName="id", nullable=true)
     * )
     */
    private $terminal;

    /**
     * @var string
     *
     * @ORM\Column(name="container_number", type="string", length=11, nullable=true)
     */
    private $containerNumber;

    /**
     * @var float
     *
     * @ORM\Column(name="container_real_size", type="string", length=11, nullable=true)
     */
    private $containerRealSize;

    /**
     * @var string
     *
     * @ORM\Column(name="from_address", type="string", length=255, nullable=false)
     */
    private $fromAddress;

    /**
     * @var Coordinates
     *
     * @ORM\Column(name="from_address_gis", type="json", nullable=true)
     */
    private $fromAddressGis;

    /**
     * @var string
     *
     * @ORM\Column(name="to_address", type="string", length=255, nullable=false)
     */
    private $toAddress;

    /**
     * @var Coordinates
     *
     * @ORM\Column(name="to_address_gis", type="json", nullable=true)
     */
    private $toAddressGis;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_unladen_address", type="string", length=255, nullable=true)
     */
    private $deliveryUnladenAddress;

    /**
     * @var Coordinates
     *
     * @ORM\Column(name="delivery_unladen_address_gis", type="json", nullable=true)
     */
    private $deliveryUnladenAddressGis;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_start", type="datetime", nullable=false)
     */
    private $dateStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="assigned", type="boolean", nullable=false)
     */
    private $assigned = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="assigned_at", type="datetime", nullable=true)
     */
    private $assignedAt;

    /**
     * @var bool
     *
     * @ORM\Column(name="canceled", type="boolean", nullable=false)
     */
    private $canceled = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_passed_invoice", type="boolean", nullable=false)
     */
    private $passedInvoice = false;

    /**
     * @var string
     *
     * @ORM\Column(name="cancel_reason", type="text", nullable=true)
     */
    private $cancelReason;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="canceled_at", type="datetime", nullable=true)
     */
    private $canceledAt;

    /**
     * @var bool
     *
     * @ORM\Column(name="successfully_completed", type="boolean", nullable=false)
     */
    private $successfullyCompleted = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="completed_at", type="datetime", nullable=true)
     */
    private $completedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @var bool
     *
     * @ORM\Column(name="archived", type="boolean", nullable=false)
     */
    private $archived = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="archived_at", type="datetime", nullable=true)
     */
    private $archivedAt;

    /**
     * @var int
     *
     * @ORM\Column(name="last_event", type="integer", nullable=false)
     */
    private $lastEvent = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="estimated_price", type="integer", nullable=false)
     */
    private $estimatedPrice = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="price", type="integer", nullable=false)
     */
    private $price = 0;

    /**
     * @Serializer\Exclude()
     * @ORM\OneToMany(targetEntity="Enot\ApiBundle\Entity\TransportationEventHistory", mappedBy="transportation")
     */
    private $events;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Transportation
     */
    public function setId(int $id): Transportation
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Driver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param Driver $driver
     * @return Transportation
     */
    public function setDriver($driver): Transportation
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * @return Vehicle
     */
    public function getVehicle()
    {
        return $this->vehicle;
    }

    /**
     * @param Vehicle $vehicle
     * @return Transportation
     */
    public function setVehicle($vehicle): Transportation
    {
        $this->vehicle = $vehicle;
        return $this;
    }

    /**
     * @return Trailer
     */
    public function getTrailer()
    {
        return $this->trailer;
    }

    /**
     * @param Trailer $trailer
     * @return Transportation
     */
    public function setTrailer(Trailer $trailer): Transportation
    {
        $this->trailer = $trailer;
        return $this;
    }

    /**
     * @return string
     */
    public function getFromAddress(): string
    {
        return $this->fromAddress;
    }

    /**
     * @param string $fromAddress
     * @return Transportation
     */
    public function setFromAddress(string $fromAddress): Transportation
    {
        $this->fromAddress = $fromAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getToAddress(): string
    {
        return $this->toAddress;
    }

    /**
     * @param string $toAddress
     * @return Transportation
     */
    public function setToAddress(string $toAddress): Transportation
    {
        $this->toAddress = $toAddress;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateStart()
    {
        return $this->dateStart->setTimezone(new \DateTimeZone("Europe/Moscow"));
    }

    /**
     * @param \DateTime $dateStart
     * @return Transportation
     */
    public function setDateStart($dateStart): Transportation
    {
        $this->dateStart = $dateStart;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt->setTimezone(new \DateTimeZone("Europe/Moscow"));
    }

    /**
     * @ORM\PrePersist()
     */
    public function setCreatedAt()
    {
        $this->createdAt = new \DateTime('now');
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Transportation
     */
    public function setDescription(string $description): Transportation
    {
        $this->description = $description;
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
     * @return Transportation
     */
    public function setExternalId(string $externalId): Transportation
    {
        $this->externalId = $externalId;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryUnladenAddress(): string
    {
        return $this->deliveryUnladenAddress;
    }

    /**
     * @param string $deliveryUnladenAddress
     * @return Transportation
     */
    public function setDeliveryUnladenAddress(string $deliveryUnladenAddress): Transportation
    {
        $this->deliveryUnladenAddress = $deliveryUnladenAddress;
        return $this;
    }

    /**
     * @return ContainerType
     */
    public function getContainerType(): ContainerType
    {
        return $this->containerType;
    }

    /**
     * @param ContainerType $containerType
     * @return Transportation
     */
    public function setContainerType(ContainerType $containerType): Transportation
    {
        $this->containerType = $containerType;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAssigned(): bool
    {
        return $this->assigned;
    }

    /**
     * @param bool $assigned
     * @return Transportation
     */
    public function setAssigned(bool $assigned): Transportation
    {
        $this->assigned = $assigned;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerNumber(): string
    {
        return $this->containerNumber;
    }

    /**
     * @param string $containerNumber
     * @return Transportation
     */
    public function setContainerNumber(string $containerNumber): Transportation
    {
        $this->containerNumber = $containerNumber;
        return $this;
    }

    /**
     * @return float
     */
    public function getContainerRealSize(): float
    {
        return $this->containerRealSize;
    }

    /**
     * @param float $containerRealSize
     * @return Transportation
     */
    public function setContainerRealSize(float $containerRealSize): Transportation
    {
        $this->containerRealSize = $containerRealSize;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getAssignedAt()
    {
        return $this->assignedAt;
    }

    /**
     * @param \DateTime $assignedAt
     * @return Transportation
     */
    public function setAssignedAt($assignedAt): Transportation
    {
        $this->assignedAt = $assignedAt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->canceled;
    }

    /**
     * @param bool $canceled
     * @return Transportation
     */
    public function setCanceled(bool $canceled): Transportation
    {
        $this->canceled = $canceled;
        return $this;
    }

    /**
     * @return string
     */
    public function getCancelReason(): string
    {
        return $this->cancelReason;
    }

    /**
     * @param string $cancelReason
     * @return Transportation
     */
    public function setCancelReason(string $cancelReason): Transportation
    {
        $this->cancelReason = $cancelReason;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCanceledAt()
    {
        return $this->canceledAt;
    }

    /**
     * @param \DateTime $canceledAt
     * @return Transportation
     */
    public function setCanceledAt($canceledAt): Transportation
    {
        $this->canceledAt = $canceledAt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSuccessfullyCompleted(): bool
    {
        return $this->successfullyCompleted;
    }

    /**
     * @param bool $successfullyCompleted
     * @return Transportation
     */
    public function setSuccessfullyCompleted(bool $successfullyCompleted): Transportation
    {
        $this->successfullyCompleted = $successfullyCompleted;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCompletedAt()
    {
        return $this->completedAt;
    }

    /**
     * @param \DateTime $completedAt
     * @return Transportation
     */
    public function setCompletedAt($completedAt): Transportation
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isArchived(): bool
    {
        return $this->archived;
    }

    /**
     * @param bool $archived
     * @return Transportation
     */
    public function setArchived(bool $archived): Transportation
    {
        $this->archived = $archived;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getArchivedAt()
    {
        return $this->archivedAt;
    }

    /**
     * @param \DateTime $archivedAt
     * @return Transportation
     */
    public function setArchivedAt($archivedAt): Transportation
    {
        $this->archivedAt = $archivedAt;
        return $this;
    }

    /**
     * @return Coordinates
     */
    public function getFromAddressGis()
    {
        return $this->fromAddressGis;
    }

    /**
     * @param Coordinates $fromAddressGis
     * @return Transportation
     */
    public function setFromAddressGis(Coordinates $fromAddressGis): Transportation
    {
        $this->fromAddressGis = $fromAddressGis;
        return $this;
    }

    /**
     * @return Coordinates
     */
    public function getToAddressGis()
    {
        return $this->toAddressGis;
    }

    /**
     * @param Coordinates $toAddressGis
     * @return Transportation
     */
    public function setToAddressGis(Coordinates $toAddressGis): Transportation
    {
        $this->toAddressGis = $toAddressGis;
        return $this;
    }

    /**
     * @return Coordinates
     */
    public function getDeliveryUnladenAddressGis()
    {
        return $this->deliveryUnladenAddressGis;
    }

    /**
     * @param Coordinates $deliveryUnladenAddressGis
     * @return Transportation
     */
    public function setDeliveryUnladenAddressGis(Coordinates $deliveryUnladenAddressGis): Transportation
    {
        $this->deliveryUnladenAddressGis = $deliveryUnladenAddressGis;
        return $this;
    }

    /**
     * @return int
     */
    public function getLastEvent(): int
    {
        return $this->lastEvent;
    }

    /**
     * @param int $lastEvent
     * @return Transportation
     */
    public function setLastEvent($lastEvent)
    {
        $this->lastEvent = $lastEvent;
        return $this;
    }

    /**
     * @return int
     */
    public function getEstimatedPrice()
    {
        return $this->estimatedPrice;
    }

    /**
     * @param int $estimatedPrice
     * @return Transportation
     */
    public function setEstimatedPrice($estimatedPrice)
    {
        $this->estimatedPrice = $estimatedPrice;
        return $this;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $price
     * @return Transportation
     */
    public function setPrice($price)
    {
        $this->price = $price;
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
     * @return Transportation
     */
    public function setDeletedAt($deletedAt): Transportation
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * @return Terminal
     */
    public function getTerminal()
    {
        return $this->terminal;
    }

    /**
     * @param Terminal $terminal
     */
    public function setTerminal(Terminal $terminal): void
    {
        $this->terminal = $terminal;
    }

    /**
     * @return bool
     */
    public function isPassedInvoice()
    {
        return $this->passedInvoice;
    }

    /**
     * @param bool $passedInvoice
     */
    public function setPassedInvoice($passedInvoice)
    {
        $this->passedInvoice = $passedInvoice;
    }

    /**
     * @return mixed
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param mixed $events
     */
    public function setEvents($events): void
    {
        $this->events = $events;
    }

    public function getCurrentEvent()
    {
        if(empty($this->events)) {
            return null;
        }

        /** @var PersistentCollection $events */
        $events = $this->events;

        return $events->last();
    }
}
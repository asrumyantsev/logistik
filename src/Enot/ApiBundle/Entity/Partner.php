<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;


/**
 * Class Partner
 * @package Enot\ApiBundle\Entity
 *
 * @ORM\Table(name="partners")
 * @ORM\Entity
 */
class Partner
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @Serializer\Groups({"Mobile"})
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
     * @ORM\Column(name="logo", type="string", length=255, nullable=true)
     */
    private $logo;

    /**
     * @var string
     *
     * @ORM\Column(name="inn", type="string", length=20, nullable=false)
     */
    private $inn;

    /**
     * @Serializer\Exclude()
     * @ORM\OneToMany(targetEntity="Enot\ApiBundle\Entity\Vehicle", mappedBy="partner")
     */
    private $vehicles;

    /**
     * @var integer
     *
     * @ORM\Column(name="balance", type="integer", nullable=false)
     */
    private $balance;

    /**
     * @var integer
     *
     * @ORM\Column(name="priority", type="integer", nullable=false)
     */
    private $priority;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var User
     *
     *
     * @Serializer\Groups({"Default"})
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $user;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
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
     */
    public function setName(string $name): void
    {
        $this->name = $name;
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
     */
    public function setExternalId(string $externalId): void
    {
        $this->externalId = $externalId;
    }

    /**
     * @return string
     */
    public function getInn(): string
    {
        return $this->inn;
    }

    /**
     * @param string $inn
     */
    public function setInn(string $inn)
    {
        $this->inn = $inn;
    }

    /**
     * @return Vehicle[]
     */
    public function getVehicles()
    {
        return $this->vehicles;
    }

    /**
     * @param mixed $vehicles
     * @return Partner
     */
    public function setVehicles($vehicles)
    {
        $this->vehicles = $vehicles;
        return $this;
    }

    /**
     * @return array
     */
    public function getTransportations()
    {
        $transportations = [];

        foreach ($this->getVehicles() as $vehicle) {
            $transportations = array_merge($transportations, $vehicle->getTransportations()->getValues());
        }

        return $transportations;
    }

    public function getActiveVehicles()
    {
        $response = [];

        foreach ($this->getVehicles() as $vehicle) {
            if($vehicle->getDeletedAt()) {
                continue;
            }

            $response[] = $vehicle;
        }

        return $response;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return Partner
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return int
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param int $balance
     * @return Partner
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return Partner
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param string $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }
}
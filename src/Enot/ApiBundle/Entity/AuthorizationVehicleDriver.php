<?php

namespace Enot\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AuthorizationStatus
 *
 * @ORM\Table(name="authorization_vehicle_driver")
 * @ORM\Entity(repositoryClass="Enot\ApiBundle\Repository\AuthorizationVehicleDriverRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class AuthorizationVehicleDriver
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Driver
     *
     * @ORM\ManyToOne(targetEntity="Enot\ApiBundle\Entity\Driver")
     * @ORM\JoinColumns(
     *     @ORM\JoinColumn(name="driver_id", referencedColumnName="id", nullable=false)
     * )
     */
    private $driver;

    /**
     * @var Vehicle
     *
     * @ORM\ManyToOne(targetEntity="Enot\ApiBundle\Entity\Vehicle")
     * @ORM\JoinColumns(
     *     @ORM\JoinColumn(name="vehicle_id", referencedColumnName="id", nullable=false)
     * )
     */
    private $vehicle;

    /**
     * @var array
     *
     * @ORM\Column(name="position", type="json", nullable=true)
     */
    private $position;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_at", type="datetime", nullable=true)
     */
    private $updateAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_at", type="datetime", nullable=true)
     */
    private $startAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_at", type="datetime", nullable=true)
     */
    private $endAt;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     *
     * @param int $id
     *
     * @return AuthorizationVehicleDriver
     */
    public function setId($id)
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
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;
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
     */
    public function setVehicle($vehicle)
    {
        $this->vehicle = $vehicle;
    }

    /**
     * @return array
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param array $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateAt()
    {
        return $this->updateAt;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     * @throws \Exception
     */
    public function setUpdateAt()
    {
        $this->updateAt = new \DateTime('now');
    }

    /**
     * @throws \Exception
     */
    public function refreshTime()
    {
        $this->updateAt = new \DateTime('now');
    }

    /**
     * @return \DateTime
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * @param \DateTime $startAt
     */
    public function setStartAt(\DateTime $startAt)
    {
        $this->startAt = $startAt;
    }

    /**
     * @return \DateTime
     */
    public function getEndAt()
    {
        return $this->endAt;
    }

    /**
     * @param \DateTime $endAt
     */
    public function setEndAt($endAt)
    {
        $this->endAt = $endAt;
    }

}

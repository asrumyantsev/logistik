<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019-09-11
 * Time: 14:36
 */

namespace Enot\ApiBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @package Enot\ApiBundle\Entity
 *
 * @ORM\Table(name="transportation_event_histories")
 * @ORM\Entity
 */
class TransportationEventHistory
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
     * @var Transportation
     *
     * @ORM\ManyToOne(targetEntity="Enot\ApiBundle\Entity\Transportation")
     * @ORM\JoinColumns(
     *     @ORM\JoinColumn(name="transportation_id", referencedColumnName="id", nullable=false)
     * )
     */
    private $transportation;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Enot\ApiBundle\Entity\Event")
     * @ORM\JoinColumns(
     *     @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=false)
     * )
     */
    private $event;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Enot\ApiBundle\Entity\Event")
     * @ORM\JoinColumns(
     *     @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     * )
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

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
     * @return Transportation
     */
    public function getTransportation(): Transportation
    {
        return $this->transportation;
    }

    /**
     * @param Transportation $transportation
     */
    public function setTransportation(Transportation $transportation): void
    {
        $this->transportation = $transportation;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date): void
    {
        $this->date = $date;
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
}
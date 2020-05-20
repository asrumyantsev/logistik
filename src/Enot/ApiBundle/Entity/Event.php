<?php

namespace Enot\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 *
 * @ORM\Table(name="event")
 * @ORM\Entity
 */
class Event
{
    const ASSIGN_DRIVER = 1,
        ARRIVAL_DELIVERY = 2,
        DEPARTURE_DELIVERY = 3,
        ARRIVAL_CLIENT = 4,
        DEPARTURE_CLIENT = 5,
        PASS_EMPTY = 6,
        FINISH = 7;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     */
    private $name;


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
     * Set name
     *
     * @param string $name
     *
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @param int $id
     *
     * @return Event
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }
}

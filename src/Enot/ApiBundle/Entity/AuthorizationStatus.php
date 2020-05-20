<?php

namespace Enot\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AuthorizationStatus
 *
 * @ORM\Table(name="authorization_statuses")
 * @ORM\Entity
 */
class AuthorizationStatus
{
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
     * @return AuthorizationStatus
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
     * @return AuthorizationStatus
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }
}

<?php

namespace Enot\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Error
 *
 * @ORM\Table(name="errors")
 * @ORM\Entity
 */
class Error
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
     * @var string
     *
     * @ORM\Column(name="en", type="string", length=250, nullable=true)
     */
    private $en;

    /**
     * @var string
     *
     * @ORM\Column(name="ru", type="string", length=250, nullable=true)
     */
    private $ru;



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
     * Set en
     *
     * @param string $en
     *
     * @return Error
     */
    public function setEn($en)
    {
        $this->en = $en;

        return $this;
    }

    /**
     * Get en
     *
     * @return string
     */
    public function getEn()
    {
        return $this->en;
    }

    /**
     * Set ru
     *
     * @param string $ru
     *
     * @return Error
     */
    public function setRu($ru)
    {
        $this->ru = $ru;

        return $this;
    }

    /**
     * Get ru
     *
     * @return string
     */
    public function getRu()
    {
        return $this->ru;
    }
}

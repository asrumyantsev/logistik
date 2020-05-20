<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @package Enot\ApiBundle\Entity
 *
 * @ORM\Table(name="container_types")
 * @ORM\Entity(repositoryClass="Enot\ApiBundle\Repository\ContainerTypeRepository")
 */
class ContainerType
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
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=100, nullable=false)
     */
    private $type;

    /**
     * @var float
     *
     * @ORM\Column(name="size", type="float")
     */
    private $size;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ContainerType
     */
    public function setId(int $id): ContainerType
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
     * @return ContainerType
     */
    public function setName(string $name): ContainerType
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ContainerType
     */
    public function setType(string $type): ContainerType
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return float
     */
    public function getSize(): float
    {
        return $this->size;
    }

    /**
     * @param float $size
     * @return ContainerType
     */
    public function setSize(float $size): ContainerType
    {
        $this->size = $size;
        return $this;
    }
}
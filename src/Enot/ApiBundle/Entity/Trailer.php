<?php

namespace Enot\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Trailer
 * @package Enot\ApiBundle\Entity
 *
 * @ORM\Table(name="trailers")
 * @ORM\Entity(repositoryClass="Enot\ApiBundle\Repository\TrailerRepository")
 */
class Trailer
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="external_id", type="string", length=255, nullable=false)
     */
    private $externalId;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Trailer
     */
    public function setId(int $id): Trailer
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
     * @return Trailer
     */
    public function setName(string $name): Trailer
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
     * @return Trailer
     */
    public function setExternalId(string $externalId): Trailer
    {
        $this->externalId = $externalId;
        return $this;
    }
}
<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Class Configuration
 * @package Enot\ApiBundle\Entity
 *
 * @ORM\Table(name="configurations")
 * @ORM\Entity(repositoryClass="Enot\ApiBundle\Repository\ConfigurationRepository")
 */
class Configuration
{
    /**
     * @var string
     *
     * @ORM\Id()
     * @ORM\Column(name="key", type="string", length=100, nullable=false)
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=false)
     */
    private $value;

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return Configuration
     */
    public function setKey(string $key): Configuration
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return Configuration
     */
    public function setValue(string $value): Configuration
    {
        $this->value = $value;
        return $this;
    }


}
<?php

namespace Enot\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Enot\ApiBundle\Services\CustomerManager;
use JMS\Serializer\Annotation as Serializer;

/**
 * Customer
 *
 * @Serializer\ExclusionPolicy("none")
 * @ORM\Table(name="customers",
 *     indexes={
 *     @ORM\Index(name="user_id", columns={"user_id"}),
 *     @ORM\Index(name="authorization_status_id", columns={"authorization_status_id"})
 * })
 * @ORM\Entity(repositoryClass="Enot\ApiBundle\Repository\CustomerRepository")
 */
class Customer
{
    /**
     * @var integer
     *
     * @Serializer\Groups({"Default"})
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @Serializer\Groups({"Default"})
     * @ORM\Column(name="first_name", type="string", length=100, nullable=true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @Serializer\Groups({"Default"})
     * @ORM\Column(name="second_name", type="string", length=100, nullable=true)
     */
    private $secondName;

    /**
     * @var User
     *
     *
     * @Serializer\Groups({"Default"})
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var AuthorizationStatus
     *
     *
     * @Serializer\Groups({"Default"})
     * @ORM\ManyToOne(targetEntity="AuthorizationStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="authorization_status_id", referencedColumnName="id")
     * })
     */
    private $authorizationStatus;


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
     * Set firstName
     *
     * @param string $firstName
     *
     * @return Customer
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set secondName
     *
     * @param string $secondName
     *
     * @return Customer
     */
    public function setSecondName($secondName)
    {
        $this->secondName = $secondName;

        return $this;
    }

    /**
     * Get secondName
     *
     * @return string
     */
    public function getSecondName()
    {
        return $this->secondName;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return Customer
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set authorizationStatus
     *
     * @param AuthorizationStatus $authorizationStatus
     *
     * @return Customer
     */
    public function setAuthorizationStatus(AuthorizationStatus $authorizationStatus = null)
    {
        $this->authorizationStatus = $authorizationStatus;

        return $this;
    }

    /**
     * Get authorizationStatus
     *
     * @return AuthorizationStatus
     */
    public function getAuthorizationStatus()
    {
        return $this->authorizationStatus;
    }

    /**
     * @return bool
     */
    public function isStatusAccepted()
    {
        return $this->getAuthorizationStatus()->getId() == CustomerManager::AUTH_STATUS_ACCEPTED_ID;
    }
}

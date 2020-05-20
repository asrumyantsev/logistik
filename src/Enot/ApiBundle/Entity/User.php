<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 * @ORM\AttributeOverrides({
 *     @ORM\AttributeOverride(name="confirmationToken",
 *         column=@ORM\Column(
 *             name="confirmation_token",
 *             type="string",
 *             length=180,
 *             unique=false,
 *             nullable=true
 *         )
 *     )
 * })
 */
class User extends BaseUser
{
    public function __construct()
    {
        parent::__construct();
        $this->setCustomer(new Customer());
        $this->getCustomer()->setUser($this);
    }


    /**
     * @var integer
     *
     * @Serializer\Groups({"Default"})
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Customer
     *
     * @Serializer\Exclude()
     * @ORM\OneToOne(targetEntity="Enot\ApiBundle\Entity\Customer", mappedBy="user", cascade={"persist", "remove"})
     */
    private $customer;

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }
}
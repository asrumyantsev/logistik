<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Services;

use Doctrine\ORM\EntityManager;
use Enot\ApiBundle\Entity\Customer;
use Enot\ApiBundle\Repository\CustomerRepository;
use Symfony\Component\HttpFoundation\Request;

class CustomerManager
{
    const
        AUTH_STATUS_ACCEPTED_ID = 1,
        AUTH_STATUS_BLOCKED_ID = 2,
        AUTH_STATUS_EXPIRED_ID = 3,
        AUTH_STATUS_INVALID_ID = 4,
        AUTH_STATUS_CONCURRENT_ID = 5;

    /** @var EntityManager $em */
    private $em;

    /** @var Customer $em */
    private $customer;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return CustomerRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('EnotApiBundle:Customer');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getAuthStatusRepository()
    {
        return $this->em->getRepository('AuthorizationVehicleDriver');
    }

    /**
     * @param Customer $customer
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function setAcceptedAuthorizationStatus(Customer $customer)
    {
        $authorizationStatus = $this->getAuthStatusRepository()->find(self::AUTH_STATUS_ACCEPTED_ID);
        $customer->setAuthorizationStatus($authorizationStatus);

        $this->save($customer);
    }

    /**
     * @param Customer $customer
     * @return bool
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function setPendingAuthorizationStatus(Customer $customer)
    {
        //only if Auth status - Accepted
        if ($customer->getAuthorizationStatus()->getId() == self::AUTH_STATUS_ACCEPTED_ID) {
            $authorizationStatus = $this->getAuthStatusRepository()->find(self::AUTH_STATUS_CONCURRENT_ID);
            $customer->setAuthorizationStatus($authorizationStatus);

            $this->save($customer);

            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * @param Request $request
     * @return Customer
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function edit(Request $request)
    {
        if ($request->request->get("first_name")) {
            $this->getCustomer()->setFirstName($request->request->get("first_name"));
        }

        if ($request->request->get("second_name")) {
            $this->getCustomer()->setSecondName($request->request->get("second_name"));
        }

        $this->save($this->getCustomer());

        return $this->getCustomer();
    }


    /**
     * @param Customer $customer
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    private function save(Customer $customer)
    {
        $this->em->persist($customer);
        $this->em->flush($customer);
    }

    /**
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     * @return CustomerManager
     */
    public function setCustomer(Customer $customer): CustomerManager
    {
        $this->customer = $customer;

        return $this;
    }
}
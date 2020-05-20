<?php

namespace Enot\ApiBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Enot\ApiBundle\Entity\Customer;
use Enot\ApiBundle\Entity\User;

class CustomerRepository extends EntityRepository
{
    /**
     * @param User $user
     * @return Customer|null|object
     */
    public function findOneByUser(User $user)
    {
        return $this->findOneBy(['user' => $user]);
    }

    /**
     * @param string $idTag
     * @return Customer|null|object
     */
    public function findOneByIdTag(string $idTag)
    {
        return $this->findOneBy(['idTag' => $idTag]);
    }
}
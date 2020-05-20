<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Repository;


use Doctrine\ORM\EntityRepository;

class ContainerTypeRepository extends EntityRepository
{
    public function findOneByExternalId($externalId)
    {
        return $this->findOneBy(['id' => $externalId]);
    }
}
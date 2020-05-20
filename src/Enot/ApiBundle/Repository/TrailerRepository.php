<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Repository;


use Doctrine\ORM\EntityRepository;

class TrailerRepository extends EntityRepository
{
    public function findOneByExternalId($externalId)
    {
        return $this->findOneBy(['externalId' => $externalId]);
    }
}
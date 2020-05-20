<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Repository;


use Doctrine\ORM\EntityRepository;

class VehicleRepository extends EntityRepository
{
    public function findOneByExternalId($externalId)
    {
        return $this->findOneBy(['externalId' => $externalId]);
    }

    public function findOneByDeviceMac($deviceMac)
    {
        return $this->findOneBy(['deviceMac' => strtolower($deviceMac), 'deletedAt' => null]);
    }

    public function findByParams($partner = null)
    {
        $statusBuilder = $this->createQueryBuilder("v1")
            ->select("s1.id")
            ->join("v1.statuses", "s1")
            ->where("s1.endAt is NULL")
            ->orderBy("s1.startAt", "DESC")
            ->setMaxResults(1);

        $query = $this->createQueryBuilder("v")->select("v, s")
            ->where("v.deletedAt is null");

        $query->leftJoin("v.statuses", "s", "WITH", $query->expr()->in("s.id", $statusBuilder->getDQL()));
        $query->groupBy("v");


        if($partner) {
            $query->andWhere("v.partner = :partner")->setParameter("partner", $partner);
        }

        return $query->getQuery()->getResult();
    }
}
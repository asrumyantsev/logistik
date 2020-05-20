<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Enot\ApiBundle\Entity\Driver;
use Enot\ApiBundle\Entity\Partner;

class DriverRepository extends EntityRepository
{
    public function findOneByExternalId($externalId)
    {
        return $this->findOneBy(['externalId' => $externalId]);
    }

    public function findOneByPhone($phone)
    {
        return $this->findOneBy(['phone' => $phone]);
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById($id)
    {
        $statusBuilder = $this->createQueryBuilder("d1")
            ->select("s1.id")
            ->join("d1.statuses", "s1")
            ->where("s1.endAt is NULL")
            ->orderBy("s1.startAt", "DESC")
            ->setMaxResults(1);

        $queryBuilder = $this->createQueryBuilder("d")->select("d, s, t")->addSelect("COUNT(t_c.id) as completed_transportation_count");
        $queryBuilder->leftJoin("d.transportations", "t", "WITH", "t.deletedAt is null");
        $queryBuilder->leftJoin("d.transportations", "t_c", "WITH", "t.id = t_c.id AND t_c.completedAt is not null");
        $queryBuilder->leftJoin("d.statuses", "s", "WITH", $queryBuilder->expr()->in("s.id", $statusBuilder->getDQL()));
        $queryBuilder->leftJoin("s.vehicle", "v");
        $queryBuilder->where("d.id = :driverId")->setParameter("driverId", $id);
        $queryBuilder->groupBy("d");

        $row = $queryBuilder->getQuery()->getOneOrNullResult();
        /** @var Driver $driver */
        $driver = $row[0];
        if (!$driver) {
            return null;
        }
        $driver->setCompletedTransportationCount($row["completed_transportation_count"]);
        return $driver;
    }

    public function findByParams($partner = null, $status = null, $additional = [])
    {
        $statusBuilder = $this->createQueryBuilder("d1")
            ->select("s1.id")
            ->join("d1.statuses", "s1")
            ->where("s1.endAt is NULL")
            ->orderBy("s1.startAt", "DESC")
            ->setMaxResults(1);

        $queryBuilder = $this->createQueryBuilder("d")->select("d, s, t")->addSelect("COUNT(t_c.id) as completed_transportation_count");
        $queryBuilder->leftJoin("d.transportations", "t", "WITH", "t.deletedAt is null");
        $queryBuilder->leftJoin("d.transportations", "t_c", "WITH", "t.id = t_c.id AND t_c.completedAt is not null");
        $queryBuilder->leftJoin("d.statuses", "s", "WITH", $queryBuilder->expr()->in("s.id", $statusBuilder->getDQL()));
        $queryBuilder->leftJoin("s.vehicle", "v");
        $queryBuilder->andWhere("d.deletedAt is null");
        $queryBuilder->groupBy("d");

        if($partner) {
            $queryBuilder->andWhere("v.partner = :partner")->setParameter('partner', $partner);
        }

        if(isset($additional["onLine"]) && $additional["onLine"]) {
            if($additional["onLine"] == 1) {
                $queryBuilder->andWhere("s.id is not null");

            } else if($additional["onLine"] == -1) {
                $queryBuilder->andWhere("s.id is null");
            }
        }

        $response = [];
        foreach ($queryBuilder->getQuery()->getResult() as $row) {
            /** @var Driver $driver */
            $driver = $row[0];
            if (!$driver) {
                continue;
            }
            $driver->setCompletedTransportationCount($row["completed_transportation_count"]);
            $response[] = $driver;
        }
        return $response;
    }
}
<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Repository;


use DateTime;
use Doctrine\ORM\EntityRepository;
use Enot\ApiBundle\Entity\Driver;
use Enot\ApiBundle\Entity\Transportation;

class TransportationRepository extends EntityRepository
{
    /**
     * @param $externalId
     * @return null|object|Transportation
     */
    public function findOneByExternalId($externalId)
    {
        return $this->findOneBy(['externalId' => $externalId]);
    }

    /**
     * @param Driver $driver
     * @return array
     */
    public function findAllNotAssignedTransportation(Driver $driver)
    {
        return $this->findBy(['driver' => $driver, 'successfullyCompleted' => false, 'archived' => false]);
    }

    public function findByParams($partner = null, $vehicle = null, $driver = null, $additional = [])
    {
        $query = $this->createQueryBuilder("t")
            ->leftJoin("t.vehicle", "v")
            ->leftJoin("t.driver", "d")
            ->where("t.deletedAt is null");

        if($partner) {
            $query->andWhere("v.partner = :partner")->setParameter("partner", $partner);
        }

        if($vehicle) {
            $query->andWhere("t.vehicle = :vehicle")->setParameter("vehicle", $vehicle);
        }

        if($driver) {
            $query->andWhere("t.driver = :driver")->setParameter("driver", $driver);
        }

        if(isset($additional['event'])) {
            $query->andWhere("t.lastEvent = :lastEvent")->setParameter("lastEvent", $additional['event']);
        }

        if(isset($additional['dateFrom']) && $additional['dateFrom']) {
            $query->andWhere("t.dateStart >= :dateFrom")->setParameter("dateFrom", DateTime::createFromFormat('d.m.Y', $additional['dateFrom']));
        }

        if(isset($additional['dateTo']) && $additional['dateTo']) {
            $query->andWhere("t.dateStart <= :dateTo")->setParameter("dateTo", DateTime::createFromFormat('d.m.Y', $additional['dateTo']));
        }

        if(isset($additional['limit'])) {
            $query->setMaxResults($additional['limit']);
        }

        if(isset($additional['assign'])) {
            $query->andWhere("t.assigned = :assigned")->setParameter("assigned", $additional['assign']);
        }

        if(isset($additional['order'])) {
            $query->orderBy("t." . $additional['order'][0], $additional['order'][1]);
        }

        return $query->getQuery()->getResult();
    }


}
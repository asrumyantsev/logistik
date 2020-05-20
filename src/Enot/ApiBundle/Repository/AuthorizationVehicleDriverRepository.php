<?php


namespace Enot\ApiBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Enot\ApiBundle\Entity\AuthorizationVehicleDriver;
use Enot\ApiBundle\Entity\Transportation;

class AuthorizationVehicleDriverRepository extends EntityRepository
{
    /**
     * @return array|AuthorizationVehicleDriver[]
     * @throws \Exception
     */
    public function findAllFree()
    {
        $this->clear();
        $qb = $this->createQueryBuilder('avd')
            ->select('avd')
            ->join("avd.vehicle", "v")
            ->join("avd.driver", "d")
            ->join("v.partner", "p")
            ->leftJoin(Transportation::class, 't', Join::WITH, '(avd.driver = t.driver or avd.vehicle = t.vehicle) and t.successfullyCompleted = false and t.deletedAt is null')
//                ->where('DATE(avd.updateAt) = :date')
            ->andWhere('avd.updateAt > :date_start')
            ->andWhere('avd.updateAt < :date_end')
            ->setParameter('date_start', (new \DateTime('now'))->format('Y-m-d 00:00:00'))
            ->setParameter('date_end', (new \DateTime('now'))->format('Y-m-d 23:59:59'))
            ->andWhere('t.id is null')
            ->orderBy("t.dateStart", "ASC")
            ->addOrderBy("p.priority", "DESC")
            ->andWhere('v.deviceMac is not null')
            ->andWhere('d.onLine != 0');
//                ->setParameter('date', (new \DateTime('now'))->format('Y-m-d'));

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $driver
     * @return AuthorizationVehicleDriver
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLastDriverStatus($driver)
    {
        $query = $this->createQueryBuilder("s")
            ->orderBy("s.startAt", "DESC")
            ->setMaxResults(1)
            ->where("s.driver = :driver")->setParameter("driver", $driver);

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $vehicle
     * @return AuthorizationVehicleDriver
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLastVehicleStatus($vehicle)
    {
        $this->clear();

        $query = $this->createQueryBuilder("s")
            ->orderBy("s.startAt", "DESC")
            ->setMaxResults(1)
            ->where("s.vehicle = :vehicle")->setParameter("vehicle", $vehicle);

        return $query->getQuery()->getOneOrNullResult();
    }

}
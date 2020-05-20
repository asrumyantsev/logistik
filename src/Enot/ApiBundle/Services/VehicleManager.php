<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Services;


use Doctrine\ORM\EntityManager;
use Enot\ApiBundle\Entity\Vehicle;
use Enot\ApiBundle\Services\Exceptions\VehicleException;
use Enot\ApiBundle\Utils\EnotError;
use Symfony\Component\HttpFoundation\Response;

class VehicleManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return \Enot\ApiBundle\Repository\VehicleRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository('EnotApiBundle:Vehicle');
    }

    /**
     * @return \Enot\ApiBundle\Repository\AuthorizationVehicleDriverRepository
     */
    public function getAuthVehicleDriverRepository()
    {
        return $this->entityManager->getRepository('EnotApiBundle:AuthorizationVehicleDriver');
    }

    /**
     * @param $name
     * @param $externalId
     * @param $deviceMac
     * @param $overweight
     * @param $departureToMkad
     * @param $foots
     * @param $partner
     * @return Vehicle
     * @throws VehicleException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createNewVehicle($name, $externalId, $deviceMac, $overweight, $departureToMkad, $foots, $partner, $overweight30)
    {
        if (!$externalId || !$deviceMac) {
            throw new VehicleException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        /** @var Vehicle $existVehicle */
        $existVehicle = $this->getRepository()->findOneByExternalId($externalId);

        if ($existVehicle) {
            $existVehicle->setDeletedAt(null);
            $this->entityManager->persist($existVehicle);
            $this->entityManager->flush();
            return $existVehicle;
        }

        $vehicle = new Vehicle();
        $vehicle->setName($name);
        $vehicle->setExternalId($externalId);
        $vehicle->setDeviceMac($deviceMac);
        if (!$overweight && !$overweight30) {
            $overweight = $this->entityManager->getRepository("EnotApiBundle:OverweightType")->find(1);
            $vehicle->setOverweight($overweight);
        } else if ($overweight) {
            $overweight = $this->entityManager->getRepository("EnotApiBundle:OverweightType")->find(2);
            $vehicle->setOverweight($overweight);
        } else if ($overweight30) {
            $overweight = $this->entityManager->getRepository("EnotApiBundle:OverweightType")->find(3);
            $vehicle->setOverweight($overweight);
        }
        $vehicle->setDepartureToMkad($departureToMkad);
        $vehicle->setFoots($foots);

        $partner = $this->entityManager->getRepository("EnotApiBundle:Partner")->findOneBy([
            "externalId" => $partner
        ]);

        $vehicle->setPartner($partner);
        $this->entityManager->persist($vehicle);
        $this->entityManager->flush();


        return $vehicle;
    }

    /**
     * @param $name
     * @param $externalId
     * @param $deviceMac
     * @param $overweight
     * @param $departureToMkad
     * @param $foots
     * @throws VehicleException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateVehicle($name, $externalId, $deviceMac, $overweight, $overweight30, $departureToMkad, $foots, $partner)
    {
        if (!$externalId) {
            throw new VehicleException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        /** @var Vehicle $existVehicle */
        $existVehicle = $this->getRepository()->findOneByExternalId($externalId);

        if (!$existVehicle) {
            throw new VehicleException(EnotError::VEHICLE_NOT_FOUND, '', Response::HTTP_BAD_REQUEST);
        }

        if ($name) {
            $existVehicle->setName($name);
        }
        if (!empty($deviceMac)) {
            /** @var Vehicle $existVehicle */
            $macVehicle = $this->getRepository()->findOneBy([
                'deviceMac' => $deviceMac,
                'deletedAt' => null
            ]);

            if ($macVehicle && $macVehicle->getId() != $existVehicle->getId()) {
                throw new VehicleException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
            }
            $existVehicle->setDeviceMac($deviceMac);
        }

        if (!$overweight && !$overweight30) {
            $overweight = $this->entityManager->getRepository("EnotApiBundle:OverweightType")->find(1);
            $existVehicle->setOverweight($overweight);
        } else if ($overweight) {
            $overweight = $this->entityManager->getRepository("EnotApiBundle:OverweightType")->find(2);
            $existVehicle->setOverweight($overweight);
        } else if ($overweight30) {
            $overweight = $this->entityManager->getRepository("EnotApiBundle:OverweightType")->find(3);
            $existVehicle->setOverweight($overweight);
        }

        if ($departureToMkad !== null) {
            $existVehicle->setDepartureToMkad($departureToMkad);
        }

        if ($foots) {
            $existVehicle->setFoots($foots);
        }
        if ($partner) {
            $partner = $this->entityManager->getRepository("EnotApiBundle:Partner")->findOneBy([
                "externalId" => $partner
            ]);
            $existVehicle->setPartner($partner);
        }

        $existVehicle->setDeletedAt(null);

        $this->entityManager->persist($existVehicle);
        $this->entityManager->flush();
    }

    /**
     * @param $externalId
     * @throws VehicleException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function deleteVehicle($externalId)
    {
        if (!$externalId) {
            throw new VehicleException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        /** @var Vehicle $existVehicle */
        $existVehicle = $this->getRepository()->findOneByExternalId($externalId);

        if (!$existVehicle) {
            throw new VehicleException(EnotError::VEHICLE_NOT_FOUND, '', Response::HTTP_BAD_REQUEST);
        }

        $existVehicle->setDeletedAt(new \DateTime());

        $this->entityManager->persist($existVehicle);
        $this->entityManager->flush();
    }
}
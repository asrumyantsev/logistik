<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Services;


use Doctrine\ORM\EntityManager;
use Enot\ApiBundle\Entity\Driver;
use Enot\ApiBundle\Services\Exceptions\DriverException;
use Enot\ApiBundle\Utils\EnotError;
use Symfony\Component\HttpFoundation\Response;

class DriverManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UserManager
     */
    private $userManager;

    public function __construct(EntityManager $entityManager, UserManager $userManager)
    {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
    }

    /**
     * @return \Enot\ApiBundle\Repository\DriverRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository('EnotApiBundle:Driver');
    }

    /**
     * @param $name
     * @param $externalId
     * @param $phone
     * @return Driver
     * @throws DriverException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function createNewDriver($name, $externalId, $phone, $partner = null)
    {
        if (!$externalId) {
            throw new DriverException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        /** @var Driver $existDriver */
        $existDriver = $this->getRepository()->findOneByExternalId($externalId);
        if ($existDriver) {
            $existDriver->setDeletedAt(null);
            $this->entityManager->persist($existDriver);
            $this->entityManager->flush();

            return $existDriver;
        }

        if (empty($phone) || substr($phone, 0, 1) !== '7') {
            throw new DriverException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        $driver = new Driver();
        $driver->setName($name);
        $driver->setExternalId($externalId);
        $driver->setPhone($this->userManager->getClearPhone($phone));

        if ($partner) {
            $partner = $this->entityManager->getRepository("EnotApiBundle:Partner")->findOneBy([
                "externalId" => $partner
            ]);
            $driver->setPartner($partner);
        }

        $this->entityManager->persist($driver);
        $this->entityManager->flush();

        return $driver;
    }

    /**
     * @param $name
     * @param $externalId
     * @param $phone
     * @param null $partner
     * @return Driver
     * @throws DriverException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateDriver($name, $externalId, $phone, $partner = null)
    {
        if (!$externalId) {
            throw new DriverException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        /** @var Driver $existDriver */
        $existDriver = $this->getRepository()->findOneByExternalId($externalId);

        if (!$existDriver) {
            throw new DriverException(EnotError::DRIVER_NOT_FOUND, '', Response::HTTP_BAD_REQUEST);
        }

        if (!empty($phone)) {
            if (substr($phone, 0, 1) !== '7') {
                throw new DriverException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
            }
            $existDriver->setPhone($this->userManager->getClearPhone($phone));
        }
        $existDriver->setName($name);
        $existDriver->setDeletedAt(null);

        if ($partner) {
            $partner = $this->entityManager->getRepository("EnotApiBundle:Partner")->findOneBy([
                "externalId" => $partner
            ]);
            $existDriver->setPartner($partner);
        }


        $this->entityManager->persist($existDriver);
        $this->entityManager->flush();

        return $existDriver;
    }

    /**
     * @param $externalId
     * @return Driver
     * @throws DriverException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function deleteDriver($externalId)
    {
        if (!$externalId) {
            throw new DriverException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        $existDriver = $this->getRepository()->findOneByExternalId($externalId);

        $existDriver->setDeletedAt(new \DateTime());

        $this->entityManager->persist($existDriver);
        $this->entityManager->flush();

        return $existDriver;
    }
}
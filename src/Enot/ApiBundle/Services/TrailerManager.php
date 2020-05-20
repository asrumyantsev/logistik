<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Services;


use Doctrine\ORM\EntityManager;
use Enot\ApiBundle\Entity\Trailer;
use Enot\ApiBundle\Services\Exceptions\TrailerException;
use Enot\ApiBundle\Utils\EnotError;
use Symfony\Component\HttpFoundation\Response;

class TrailerManager
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
     * @return \Enot\ApiBundle\Repository\TrailerRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository('EnotApiBundle:Trailer');
    }

    /**
     * @param $name
     * @param $externalId
     * @return Trailer
     * @throws TrailerException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createNewTrailer($name, $externalId)
    {
        if (!$externalId) {
            throw new TrailerException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        $existTrailer = $this->getRepository()->findOneByExternalId($externalId);

        if ($existTrailer) {
            throw new TrailerException(EnotError::ENTITY_ALREADY_EXIST, '', Response::HTTP_BAD_REQUEST);
        }

        $trailer = new Trailer();
        $trailer->setName($name);
        $trailer->setExternalId($externalId);

        $this->entityManager->persist($trailer);
        $this->entityManager->flush();

        return $trailer;
    }
}
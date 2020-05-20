<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Services;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Enot\ApiBundle\Entity\Partner;
use Enot\ApiBundle\Entity\User;
use Enot\ApiBundle\Entity\Vehicle;
use Enot\ApiBundle\Services\Exceptions\VehicleException;
use Enot\ApiBundle\Utils\EnotError;
use Symfony\Component\HttpFoundation\Response;

class PartnerManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    public function __construct(EntityManager $entityManager, UserManager $userManager, \Swift_Mailer $mailer)
    {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->mailer = $mailer;
    }

    /**
     * @return EntityRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository('EnotApiBundle:Partner');
    }

    /**
     * @param $name
     * @param $externalId
     * @param $inn
     * @param $phone
     * @param $email
     * @param $password
     * @return Partner
     * @throws VehicleException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create($name, $externalId, $inn, $phone, $email, $password)
    {
        if (!$externalId) {
            throw new VehicleException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        $existPartner = $this->getRepository()->findOneBy([
            "externalId" => $externalId
        ]);

        if ($existPartner) {
            throw new VehicleException(EnotError::ENTITY_ALREADY_EXIST, '', Response::HTTP_BAD_REQUEST);
        }


        $partner = new Partner();
        $partner->setName($name);
        $partner->setExternalId($externalId);
        $partner->setInn($inn);
        $partner->setPriority(1);
        $partner->setBalance(0);
        $partner->setCreatedAt(new \DateTime());
        $this->userManager->createUser($phone, $email, $password, true);
        $user = $this->entityManager->getRepository("EnotApiBundle:User")->findOneBy([
            'username' => $phone
        ]);
        $partner->setUser($user);
        $message = "Данные о пользователе: \n Email: " . $user->getEmail();
        $message .= "\nПароль: " . $password;
        $swiftMessage = (new \Swift_Message("Изменение данных партнера"))
            ->setFrom("system@deltransmsk.ru")
            ->setTo($user->getEmail())
            ->setBody($message, 'text/html');
        $this->mailer->send($swiftMessage);
        try {
            $this->entityManager->persist($partner);
            $this->entityManager->flush();
        } catch(\Exception $e) {
            echo $e->getMessage();die();
        }



        return $partner;
    }

    /**
     * @param $name
     * @param $externalId
     * @param $inn
     * @return Partner
     * @throws VehicleException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function update($name, $externalId, $inn, $phone, $email, $password)
    {
        if (!$externalId) {
            throw new VehicleException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        /** @var Partner $existPartner */
        $existPartner = $this->getRepository()->findOneBy([
            "externalId" => $externalId
        ]);

        if (!$existPartner) {
            throw new VehicleException(EnotError::ENTITY_ALREADY_EXIST, '', Response::HTTP_BAD_REQUEST);
        }

        if ($name) {
            $existPartner->setName($name);
        }

        if ($inn) {
            $existPartner->setInn($inn);
        }
        /** @var User $user */
        $user = $existPartner->getUser();
        if ($phone) {
            $user->setUsername($phone);
        }

        if ($email) {
            $user->setEmail($email);
        }

        if ($password) {
            $user->setPlainPassword($password);
        }

        $this->entityManager->persist($existPartner);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $existPartner;
    }
}
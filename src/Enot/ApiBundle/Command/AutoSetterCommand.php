<?php

namespace Enot\ApiBundle\Command;

use Enot\ApiBundle\Entity\AuthorizationVehicleDriver;
use Enot\ApiBundle\Entity\Driver;
use Enot\ApiBundle\Entity\Transportation;
use Enot\LogBundle\Services\LogInterface;
use Enot\LogBundle\Utils\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AutoSetterCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('enot_api:auto_setter')
            ->setDescription('');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Enot\ApiBundle\Provider\ProviderException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $httpManager = $this->getContainer()->get('http.client');
        $vehicleManager = $this->getContainer()->get('enot_api.services.vehicle_manager');
        /** @var Transportation $transportation */
        $autoManager = $this->getContainer()->get('enot_api.services.auto_setter_manager');
        $transportationManager = $this->getContainer()->get('enot_api.services.transportation_manager');
        $transportations = $transportationManager->getRepository()->findBy([
            "driver" => null,
            "vehicle" => null,
            "deletedAt" => null
        ]);

        $transportationCount = 0;

        foreach ($transportations as $transportation) {

            /** @var AuthorizationVehicleDriver $driver */
            $driver = $autoManager->getNearbyVehicleDriver($transportation->getFromAddressGis()["latitude"] . "," . $transportation->getFromAddressGis()["longitude"]);
            if (!$driver) {
                continue;
            }

            $transportation->setDriver($driver->getDriver());
            $transportation->setVehicle($driver->getVehicle());
            $entityManager->persist($transportation);
            $entityManager->flush($transportation);
            $entityManager->clear();
            $transportationCount++;

            $transportationManager->getProvider()->sendDriverNotAssigned($transportation, !$transportation->getDriver());
        }

        if (empty($transportations)) {
            return;
        }

        if(!$transportationCount){
            return;
        }

        $date = new \DateTime();

        $httpManager->setHeaderAuthName("Authorization");
        $response = $httpManager->post("https://botapi.andersdev.ru/api/v1/messages/", json_encode([
            "text" => "Заявок в резерве: " . count($transportations) . "\nЗаявок распределилось: " . $transportationCount . "\nСвободных водителей: " . count($vehicleManager->getAuthVehicleDriverRepository()->findAllFree()),
            "scheduled_at" => $date->format("Y-m-d\TH:i:s")
        ]), "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoxLCJleHAiOjE1Njg2NDcyNTksInN1YiI6ImFjY2VzcyJ9.vVGbOqDlbhQx-ZhgHmFNedH5HJ3cIA_eT29ddPqpdIU");
        echo $response;

        $logger = $this->getContainer()->get("enot_log.logger");
        $requestId = Logger::getGuid();

        //getting request body as array
        $context = $response;

        //parse domain as ControllerName
        $domain = "AutoSetterCommand";

        //parse event as ActionName
        $logEvent = "sendMessage";

        //saving user and request id
        $logger->setParameters(null, $requestId);

        //make a log
        $logger->log($context, $logEvent, $domain, $logger::CATEGORY_REQUEST);
    }
}

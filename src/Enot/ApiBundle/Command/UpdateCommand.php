<?php

namespace Enot\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('enot_api:update')
            ->addArgument('key', InputArgument::REQUIRED, '0=drivers 1=vehicles 2=trailers')
            ->setDescription('Updated drivers/vehicles/trailers in dictionary (0=drivers 1=vehicles 2=trailers)');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Enot\ApiBundle\Provider\ProviderException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = $input->getArgument('key');

        $transportationManager = $this->getContainer()->get('enot_api.services.transportation_manager');
        $response = $transportationManager->updateDictionary($key);
        $output->writeln('New elements: '. $response);
    }
}

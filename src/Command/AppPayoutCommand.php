<?php

namespace App\Command;

use App\Entity\Payouts;
use App\Service\PayoutProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppPayoutCommand extends Command
{
    protected static $defaultName = 'app:payout';

    public function __construct(PayoutProcessor $payoutProcessor, ContainerInterface $container)
    {
        $this->PayoutProcessor = $payoutProcessor;
        $this->container = $container;

        parent::__construct();

    }


    protected function configure()
    {
        $this
            ->setDescription('Process all open transactions in the Database. Please read the Readme.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if($this->container->getParameter('payout_mode') != 'timed'){
            $io->error('Staged Mode is activated. No payouts by using this cron. (To force payouts use --force');

            return Command::FAILURE;

        }


        if($this->PayoutProcessor->processPayouts()) {
            $io->success('Processed.');

            return Command::SUCCESS;
        }

        $io->error('Error! Please check your balance.');

        return Command::FAILURE;






    }
}

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

class AppPayoutCommand extends Command
{
    protected static $defaultName = 'app:payout';

    public function __construct(ManagerRegistry $doctrine, EntityManagerInterface $em, PayoutProcessor $payoutProcessor)
    {
        $this->doctrine = $doctrine;
        $this->em = $em;
        $this->PayoutProcessor = $payoutProcessor;

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

        $doctrine = $this->doctrine;
        $em = $this->em;

        if($_ENV['PAYOUT_MODE'] != 'timed'){
            $io->error('Stages Mode is activated. No payouts by using this cron. (To force payouts use --force');

            return Command::FAILURE;

        }


        if($this->PayoutProcessor->processPayouts()){
            $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

            return Command::SUCCESS;
        } else {
            $io->error('Error! Please check your balance.');

            return Command::FAILURE;
        }





    }
}

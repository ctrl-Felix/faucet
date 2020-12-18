<?php

// src/Service/PayoutProcessor.php
namespace App\Service;

use App\Entity\Payouts;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use JsonRPC\Client;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PayoutProcessor
{
    public function __construct(ManagerRegistry $doctrine, EntityManagerInterface $em)
    {
        $this->doctrine = $doctrine;
        $this->em = $em;


    }

    public function processPayouts(): bool
    {
        $doctrine = $this->doctrine;
        $em = $this->em;

        //If staged Payouts -> Check if payout needs to be done!
        if($_ENV['PAYOUT_MODE'] == 'staged'){
            $count = $payouts = $doctrine->getRepository(Payouts::class)
                ->stagedPayouts()[1];
            if ($count < $_ENV['STAGED_PAYOUTS']){
                return false;
            }
        }


        $url = "http://".$_ENV['RPCUSER'].":".$_ENV['RPCPASSWORD']."@".$_ENV['RPCHOST'].":".$_ENV['RPCPORT'];



        try{
            $client = new Client($url);
            $balance = $client->execute('getbalance');
        } catch (Exception $e) {
            echo "rpc error";
            return false;
        }
        $payouts = $doctrine->getRepository(Payouts::class)
            ->getOpenPayouts();


        if(!$payouts){
            echo "No Payouts";
            return true;
        }

        //Check if there is enough balance
        $payoutamount = 0;
        foreach($payouts as $payout){
            $payoutamount += $payout->getAmount();
        }

        if($payoutamount >= $balance){
            //Not enough balance
            echo "Not enough balance";
            return false;
        }

        if($_ENV['SINGLE_PAYOUT'] == 'yes'){
            //Single TX per Payout
            foreach($payouts as $payout){
                $tx = $client->execute('sendtoaddress',array($payout->getAddress(),$payout->getAmount()));
                $payout->setTx($tx);
                $em->persist($payout);
            }
            $em->flush();




        } elseif ($_ENV['SINGLE_PAYOUT'] == 'no') {
            $sendmany = array();
            foreach($payouts as $payout){
                if (array_key_exists($payout->getAddress(), $sendmany)) {
                    //Address is already in sendmany -> add amount
                    $sendmany[$payout->getAddress()] += $payout->getAmount();
                } else {
                    //create new entry
                    $sendmany[$payout->getAddress()] = $payout->getAmount();

                }
            }
            $tx = $client->execute('sendmany',array("",$sendmany));
            foreach($payouts as $payout) {
                $payout->setTx($tx);
                $em->persist($payout);
            }
            $em->flush();


        }

        return true;
    }
}
<?php

namespace App\Twig;

use Doctrine\Persistence\ManagerRegistry;
use JsonRPC\Client;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Entity\Payouts;

class FaucetValuesExtension extends AbstractExtension
{

    public function __construct(ManagerRegistry $doctrine){
        $this->doctrine =  $doctrine;
    }

    public function getFilters(): array
    {
        return [
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('count', [$this, 'count']),
            new TwigFunction('sum', [$this, 'sum']),
            new TwigFunction('stagedPayouts', [$this, 'stagedPayouts']),
            new TwigFunction('balance', [$this, 'balance'])
        ];
    }

    public function count()
    {

        $doctrine = $this->doctrine;
        return $doctrine->getRepository(Payouts::class)
            ->countPayouts()[1];
    }

    public function sum()
    {

        $doctrine = $this->doctrine;
        return $doctrine->getRepository(Payouts::class)
            ->sumPayouts()[1];
    }

    public function stagedPayouts()
    {

        $doctrine = $this->doctrine;
        return $doctrine->getRepository(Payouts::class)
            ->stagedPayouts()[1];
    }

    public function balance()
    {
        $url = "http://".$_ENV['RPCUSER'].":".$_ENV['RPCPASSWORD']."@".$_ENV['RPCHOST'].":".$_ENV['RPCPORT'];
        try{
            $client = new Client($url);
            $balance = $client->execute('getbalance');
            return $balance;
        } catch (Exception $e) {
            return "Wallet Error";
        }


    }
}

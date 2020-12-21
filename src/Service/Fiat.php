<?php

// src/Service/Fiat.php
namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Fiat {
    public function __construct(HttpClientInterface $client, ContainerInterface $container) {
        $this->client = $client;
        $this->container = $container;
    }
    public function getAmount(): float {
        $min = $this->container->getParameter('claim_min');
        $max = $this->container->getParameter('claim_max');

        $client = $this->client;

        //Coingecko Endpoint

        $currency_assoc = array(
          '€' => 'eur',
          '$' => 'usd'
        );

        if (mb_substr($min, -1) == mb_substr($max, -1) and array_key_exists(mb_substr($min, -1), $currency_assoc)){
            $curcode = $currency_assoc[mb_substr($min, -1)];
            $url = "https://api.coingecko.com/api/v3/simple/price?ids=".$max = $this->container->getParameter('coinname')."&vs_currencies=".$curcode;
            try {
                $response = $client->request('GET', $url)->toArray();
                $price = $response[strtolower($this->container->getParameter('coinname'))][$curcode];

                //Get random payout amount in Fiat
                $min_float = floatval(mb_substr($min, 0, -1));
                $max_float = floatval(mb_substr($max, 0, -1));
                $fiat = floatval($min_float+lcg_value()*(abs($max_float-$min_float)));

                $crypto = $fiat / $price;

            } catch (\Exception $e){
                return -1;
            }

            return $crypto;



        } else {
            return -1;
        }
        //get currency

        return 1;

    }
}
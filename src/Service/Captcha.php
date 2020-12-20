<?php

// src/Service/Captcha.php
namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Captcha {
    public function __construct(HttpClientInterface $client, ContainerInterface $container) {
        $this->client = $client;
        $this->container = $container;
    }
    public function validateCaptcha($captcha): bool {
        $client = $this->client;
        if($captcha == null){
            return false;
        }

        $response = $client->request('POST', 'https://hcaptcha.com/siteverify', [
            'body' => [
                'secret' => $this->container->getParameter('secret'),
                'response' => $captcha
            ],
        ]);
        $response = $response->toArray();
        if(!$response['success']){
            return false;
        }

        return true;
    }
}
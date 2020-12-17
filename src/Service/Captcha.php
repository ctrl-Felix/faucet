<?php

// src/Service/Captcha.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Captcha {
    public function __construct(HttpClientInterface $client) {
        $this->client = $client;
    }
    public function validateCaptcha($captcha): bool {
        $client = $this->client;
        if($captcha == null){
            return false;
        }
        $response = $client->request('POST', 'https://hcaptcha.com/siteverify', [
            'body' => [
                'secret' => $_ENV['CAPTCHA_SECRET'],
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
<?php

namespace App\Controller;

use App\Entity\Payouts;
use App\Form\ClaimType;
use App\Service\Captcha;
use JsonRPC\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Twig\FaucetValuesExtension;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="app_default_faucet")
     */
    public function index(Request $request, Captcha $captcha): Response
    {
        $payout = new Payouts();
        $form = $this->createForm(ClaimType::class, $payout);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            //Check if captcha is valid
            if(!$captcha->validateCaptcha($request->request->get('h-captcha-response'))){
                //Captcha not valid
                $this->addFlash(
                    'error',
                    'Please submit a valid captcha.'
                );
                return $this->redirectToRoute('app_default_faucet');
            }


            //Check if Ip has already claimed. If ip can't be resolved create an error
            $ip = $request->getClientIp();
            if($ip == 'unknown'){
                $allowclaim = true;
            } else {
                $allowclaim = $this->getDoctrine()
                    ->getRepository(Payouts::class)
                    ->checkAddressAndIp($payout->getAddress(), $ip);
            }

            if($allowclaim){
                $this->addFlash(
                    'error',
                    'You already claimed. Please wait a bit.'
                );
                return $this->redirectToRoute('app_default_faucet');
            }

            if (!$this->validateAddress($payout->getAddress())){
                $this->addFlash(
                    'error',
                    'Invalid address.'
                );
                return $this->redirectToRoute('app_default_faucet');
            }

            //Generate Payout Amount
            $amount = round($this->random_float($_ENV['CLAIM_MIN'],$_ENV['CLAIM_MAX']), 8);
            $payout->setAmount($amount);


            $payout->setTime(new \DateTime());
            $payout->setIp($ip);
            $em = $this->getDoctrine()->getManager();
            $em->persist($payout);
            $em->flush();

            $this->addFlash(
                'success',
                'You claimed '.$amount
            );
            return $this->redirectToRoute('app_default_faucet');





        }




        return $this->render('default/faucet.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    private function random_float ($min,$max) {
        return ($min+lcg_value()*(abs($max-$min)));
    }

    private function validateAddress($address){
        $url = "http://".$_ENV['RPCUSER'].":".$_ENV['RPCPASSWORD']."@".$_ENV['RPCHOST'].":".$_ENV['RPCPORT'];


        try{
            $client = new Client($url);
            $verify = $client->execute('validateaddress', array($address));
            if($verify['isvalid']){
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }


}

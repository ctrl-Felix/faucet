<?php

namespace App\Controller;

use App\Entity\Payouts;
use App\Form\ClaimType;
use App\Service\Captcha;
use App\Service\Fiat;
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
    public function app_default_faucet(Request $request, Captcha $captcha, Fiat $fiat): Response
    {
        $payout = new Payouts();
        $form = $this->createForm(ClaimType::class, $payout);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            //Check if captcha is valid
            print_r($request->request->get('validateClaim'));
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
                $denyclaim = true;
            } else {
                $denyclaim = $this->getDoctrine()
                    ->getRepository(Payouts::class)
                    ->checkAddressAndIp($payout->getAddress(), $ip);
            }





            if($denyclaim){
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
            $min = $this->getParameter('claim_min');
            $max = $this->getParameter('claim_max');
            if(str_ends_with($min,'€') or str_ends_with($min,'$')){
                $amount = $fiat->getAmount();
                if ($amount == -1){
                    //something went wrong
                    $this->addFlash(
                        'error',
                        'Something went wrong.'
                    );
                    return $this->redirectToRoute('app_default_faucet');
                }
            } else {
                $amount = round($this->random_float($min,$max), 8);
            }

            $payout->setAmount($amount);
            $payout->setTime(new \DateTime());
            $payout->setIp($ip);
            $em = $this->getDoctrine()->getManager();
            $em->persist($payout);
            $em->flush();

            $this->addFlash(
                'success',
                'You claimed '.$amount.' '.$request->request->get('validateClaim')
            );
            return $this->redirectToRoute('app_default_faucet');

        }

        return $this->render('default/faucet.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/transactions", name="app_default_transactions")
     */
    public function app_default_transactions(): Response {
        $tx = $this->getDoctrine()
            ->getRepository(Payouts::class)
            ->getLastPayouts();

        return $this->render('default/transactions.html.twig',[
            'transactions' => $tx,
        ]);
    }



    private function random_float ($min,$max) {
        return ($min+lcg_value()*(abs($max-$min)));
    }

    private function validateAddress($address){
        $url = "http://".$this->getParameter('rpcuser').":".$this->getParameter('rpcpassword')."@".$this->getParameter('rpchost').":".$this->getParameter('rpcport');


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

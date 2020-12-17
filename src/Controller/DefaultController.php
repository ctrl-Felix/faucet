<?php

namespace App\Controller;

use App\Entity\Payouts;
use App\Form\ClaimType;
use App\Service\Captcha;
use App\Service\hCaptcha;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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


    function random_float ($min,$max) {
        return ($min+lcg_value()*(abs($max-$min)));
    }
}

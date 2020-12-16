<?php

namespace App\Controller;

use App\Entity\Payouts;
use App\Form\ClaimType;
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
    public function index(Request $request, HttpClientInterface $client): Response
    {
        $payout = new Payouts();
        $form = $this->createForm(ClaimType::class, $payout);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            //Check if captcha is valid
            if($request->request->get('h-captcha-response') == null){
                //no captcha filled
                $this->addFlash(
                    'error',
                    'Please submit a valid captcha.'
                );
                return $this->redirectToRoute('app_default_faucet');
            }
            $captchaCode = $request->request->get('h-captcha-response');
            $response = $client->request('POST', 'https://hcaptcha.com/siteverify', [
                'body' => [
                    'secret' => $_ENV['CAPTCHA_SECRET'],
                    'response' => $captchaCode
                ],
            ]);
            $response = $response->toArray();
            if(!$response['success']){
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

            $payout->setTime(new \DateTime());
            $payout->setIp($ip);
            $em = $this->getDoctrine()->getManager();
            $em->persist($payout);
            $em->flush();



        }




        return $this->render('default/faucet.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

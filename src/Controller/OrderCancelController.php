<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

class OrderCancelController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/commande/erreur/{stripeSessionId}', name: 'app_order_cancel')]
    public function index(OrderRepository $orderRepository, $stripeSessionId, MailerInterface $mailer, Request $request): Response
    {
        $user = $this->getUser();
        
        if (!$request->getSession()->get('email_sent_' . $stripeSessionId)) {
            $email = (new TemplatedEmail())
                ->from(new Address('gruut.company1@gmail.com', 'Gruut'))
                ->to($user->getEmail())
                ->subject('Annulation de commande')
                ->htmlTemplate('order_cancel/email.html.twig')
                // context sert à envoyer des paramètres, des variables dans le mail.
                ->context(['order' => $orderRepository->findOneByStripeSessionId($stripeSessionId) ])
            ;
    
            $mailer->send($email);
            // ici on initialise une session "email_sent" qui permet d'éviter le spam d'envoie d'email.
            $request->getSession()->set('email_sent_' . $stripeSessionId, true);
        }
        return $this->render('order_cancel/index.html.twig', [
            'order' => $orderRepository->findOneByStripeSessionId($stripeSessionId)
        ]);
    }
}

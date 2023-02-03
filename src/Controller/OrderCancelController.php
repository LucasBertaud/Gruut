<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderCancelController extends AbstractController
{
    #[Route('/commande/erreur/{stripeSessionId}', name: 'app_order_cancel')]
    public function index(OrderRepository $orderRepository, $stripeSessionId): Response
    {
        
        return $this->render('order_cancel/index.html.twig', [
            'order' => $orderRepository->findOneByStripeSessionId($stripeSessionId)
        ]);
    }
}

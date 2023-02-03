<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Product;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    #[Route('/commande/merci/{stripeSessionId}', name: 'app_order_validate')]
    public function index(OrderRepository $orderRepository,
     $stripeSessionId,
     ProductRepository $productRepository, 
     EntityManagerInterface $entityManager,  
     Cart $cart     
     ): Response
    {

        $order = $orderRepository->findOneByStripeSessionId($stripeSessionId);

        if(!$order || $order->getUser() != $this->getUser()){
            return $this->redirectToRoute('app_home');
                }

        foreach($order->getOrderDetails()->getValues() as $quantity){
            $productAll = $productRepository->findOneByName($quantity->getProduct());
            $stockFinal = $productAll->getStock() - $quantity->getQuantity();          
            $productAll->setStock($stockFinal);                        
            $entityManager->persist($productAll);            
        }

        if(!$order->getisPaid()){
            $cart->deleteAll();
            $order->setisPaid(1);
            $entityManager->flush();        
        }
        
        return $this->render('order_validate/index.html.twig', compact('order'));
   
    }

}

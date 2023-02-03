<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Product;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Checkout\Session;
use Stripe\Stripe;



class StripeController extends AbstractController
{
    #[Route('/commande/create_session/{reference}', name: 'app_stripe')]
    public function index(EntityManagerInterface $entityManager, Cart $cart, $reference, OrderRepository $orderRepository)
    {


        header('Content-Type: application/json');
        $YOUR_DOMAIN = 'http://5.135.101.252:8000';

        $order = $orderRepository->findOneByReference($reference);
        

        if(!$order){
            return $this->redirectToRoute('order_index');
        }
        
        Stripe::setApiKey('sk_test_51MWwyMIW8L6ku7Gn8QtxZpbCXCS6KXnoEcKVQk7r4XMHvZUOx1jfmdjfpArLaHRXfEYQtykqLmhPWkQHHU8Sx87000Az54CN6x');


        foreach($order->getOrderDetails()->getValues() as $product){
        $product_object = $entityManager->getRepository(Product::class)->findOneByName($product->getProduct());
            
        $product_for_stripe [] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $product->getPrice(),
                'product_data' => [
                    'name' => $product->getProduct(),
                    'images' => [$YOUR_DOMAIN."/images/". $product_object->getIllustration()],
                ],
            ],

            'quantity' => $product->getQuantity(),

            ];  
        }

        $product_for_stripe [] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $order->getCarrierPrice(),
                'product_data' => [
                    'name' => 'Livraison',
                    'images' => [$YOUR_DOMAIN],
                ],
            ],

            'quantity' => '1',

            ];  
        
        $checkout_session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'line_items' => [
                $product_for_stripe
                ],
                'mode' => 'payment',
                'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
                'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
            ]);   
            
         $order->setStripeSessionId($checkout_session->id);
         
         $entityManager->flush();

         return $this->redirect($checkout_session->url);         
            
    }          
}
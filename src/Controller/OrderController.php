<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\OrderType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



#[Route('/commande', name: 'order_')]
class OrderController extends AbstractController
{

    function __construct(private entityManagerInterface $entityManager){}        
    
    #[Route('/', name: 'index')]
    public function index(Cart $cart, Request $request, PaginatorInterface $paginator): Response
    {   
        
        
        if(!$this->getUser()->getAddresses()->getValues()){
            return $this->redirectToRoute('address_index');
        }

        $data = $cart->getFull();

        $articles = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1), 
            3);

        
        $form = $this->createForm(OrderType::class, null , ['user'=> $this->getUser()]);        
        return $this->render('order/index.html.twig', [
            'form' => $form->createView(), 
            'total' =>$cart->getTotal(),
            'cart' =>$articles          
                ]);
    }

    #[Route('/commande/recapitulatif', name: 'recap', methods:'POST')]
    public function add(Cart $cart, Request $request, PaginatorInterface $paginator): Response
    {
        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $date = new DateTimeImmutable();
            $carriers = $form->get('carrier')->getData();
            $delivery = $form->get('addresse')->getData();
            $delivery_content = $delivery->getFirstname().' '.$delivery->getLastname();
            $delivery_content .= '<br>'.$delivery->getPhone();

            if($delivery->getCompany()){
                $delivery_content .= '<br>'.$delivery->getCompany();
            }               
            $delivery_content .= '<br>'.$delivery->getAddress();
            $delivery_content .= '<br>'.$delivery->getPostal().' '.$delivery->getCity();
            $delivery_content .= '<br>'.$delivery->getCountry();    

            $order = new Order();
            $reference = $date->format('dmY').'-'.uniqid();
            $order->setReference($reference);
            $order->setUser($this->getUser());
            $order->setCreatedAt($date);
            $order->setCarrierName($carriers->getName());
            $order->setCarrierPrice($carriers->getPrice());
            $order->setDelivery($delivery_content);
            $order->setState(0);

            $this->entityManager->persist($order);

            foreach ($cart->getFull() as $product) {
                $orderDetails = new OrderDetails();
                $orderDetails->setMyOrder($order);
                $orderDetails->setProduct($product['product']->getName());
                $orderDetails->setQuantity($product['quantity']);
                $orderDetails->setPrice($product['product']->getPrice());
                $orderDetails->setTotal($product['product']->getPrice() * $product['quantity']);
                $this->entityManager->persist($orderDetails);

            }


            // $this->entityManager->flush();

// ------------------------------------------------------------------------------------------------ //

            $data = $cart->getFull();

            $articles = $paginator->paginate(
                $data,
                $request->query->getInt('page', 1), 
                3);
// ------------------------------------------------------------------------------------------------ //


        return $this->render('order/add.html.twig', [
        'cart' => $articles,
        'carrier' => $carriers,
        'delivery' => $delivery_content,
        'reference' => $order->getReference()
    ]);
        }
        return $this->redirectToRoute('cart');
    }

}

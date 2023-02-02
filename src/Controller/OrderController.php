<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Form\OrderType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



#[Route('/commande', name: 'order_')]
class OrderController extends AbstractController
{
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
}

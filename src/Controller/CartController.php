<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/mon-panier', name: 'cart_')]
class CartController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(SessionInterface $session): Response
    {
        
        return $this->render('cart/index.html.twig');
    }


    #[Route('/add/{id}', name: 'add')]
    public function add(cart $cart, $id , SessionInterface $session, Product $product){

        $id = $product->getId();
        $cart->add($id, $session);
        dd($session->get('cart'));
        return $this->redirectToRoute('cart_index', compact("cart"));
    }

}

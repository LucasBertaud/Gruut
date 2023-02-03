<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mon-panier', name: 'cart_')]
class CartController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Cart $cart): Response
    {
        return $this->render('cart/index.html.twig', [
            'cart' => $cart->getFull(),
            'total' => $cart->getTotal()
        ]);
    }


    #[Route('/add/{id}', name: 'add')]
    public function add(Product $product, Cart $cart, Request $request)
    {
        $cart = $cart->add($product->getId());
        $referer = $request->headers->get('referer');
        return $this->redirect($referer);

    }
      
    #[Route('/decrease/{id}', name: 'decrease')]
    public function decrease(Product $product, Cart $cart)
    {

        $cart->decrease($product->getId());
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Product $product, Cart $cart)
    {
        $cart->delete($product->getId());
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/delete', name: 'delete_all')]
    public function deleteAll(Cart $cart): Response
    {
        $cart->deleteAll();
        return $this->redirectToRoute('cart_index');
    }
}



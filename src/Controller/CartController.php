<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
    public function add(Product $products, Cart $cart, Request $request, SessionInterface $session)
    {
        $referer = $request->headers->get('referer');
        $session->remove('inputOfValue');
        if(strpos($referer, "http://5.135.101.252:8000/nos-categories") !== false){
            $inputOfValue = $request->request->get('inputOfValue');
            if ($inputOfValue !== null) {
                $session->set('inputOfValue', $inputOfValue);
            }
            $inputOfValue = $session->get('inputOfValue');
            for($i = 0; $i < $inputOfValue; $i++){
                $cart->add($products->getId());
            }   
        }
        else{
            $cart->add($products->getId());
        }
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



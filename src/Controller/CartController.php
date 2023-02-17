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
        // code pour rester sur la page en cours(redirection automatique sur la page précédente) aprés ajout du produit
        $referer = $request->headers->get('referer');
        $session->remove('inputOfValue');
        // strpos permet de vérifier, entre la variable $referer qui correspond à l'url précédente et une partie du chemin indiqué( ex http://5.135.101.252/jambon/fromage) > si on trouve  http://5.135.101.252/jambon sa fonctionne car l'occurence est trouvé.
        if(strpos($referer, "http://5.135.101.252:8000/nos-categories") !== false || $referer == "http://5.135.101.252:8000/" || strpos($referer, "http://5.135.101.252:8000/produit") !== false){
            // on souhaite récupérer la donnée de la variable envoyé d'ajax, pour cela on la récupère en faisant un $request->request. Néanmoins, si vous faites un dd($request), la valeur sera null car étant donné que c'est de l'asynchrone, la valeur sera déjà passé.
            $inputOfValue = $request->request->get('inputOfValue');
            if ($inputOfValue !== null) {
                // on sauvegarde temporairement la variable inputOfValue dans une session
                $session->set('inputOfValue', $inputOfValue);
            }
            $inputOfValue = $session->get('inputOfValue');
            
            // on boucle avec la valeur de inputOfValue, à l'intérieur de la boucle, on appelle la classe $cart puis la fonction add.
            for($i = 0; $i < $inputOfValue; $i++){
                $cart->add($products->getId());                
            }   
        }
        else{
            // si inputOfValue n'existe pas, alors on ne rajoute qu'une fois le produit
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



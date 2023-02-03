<?php

namespace App\Classe;

use App\Entity\Categories;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class qui gére les différentes opérations sur le panier
 */
class Cart
{

    public function __construct(private RequestStack $request, private EntityManagerInterface $entityManager, private ProductRepository $productRepository)
    {
    }

    public function __toString()
    {
        return $this->name;
    }


    /**
     * Fonction qui permet d'ajouter des produits dans le panier
     *
     * @param Integer $id -> Id du produit
     * @return self
     */
    
    public function add($id)
    {
        $session =  $this->request->getSession();

        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $session->set('cart', $cart);

        return $session->get('cart');
    }


    // public function get(){

    //   $session = $this->request->getSession();

    //   return $session->get('cart');
    // }


    /**
     * Fonction pour décrémenter les articles du panier
     *
     * @param  integer $id -> Id du produit
     * @return self
     */
    public function decrease($id)
    {
        $session =  $this->request->getSession();

        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            if ($cart[$id] > 1) {
                $cart[$id]--;
            } else {
                unset($cart[$id]);
            }
        }

        $session->set('cart', $cart);

        return $session->get('cart');
    }
    /**
     * Fonction pour supprimer un article
     *
     * @param Integer $id -> Id du produit
     * @return self
     */
    public function delete($id)
    {

        $session =  $this->request->getSession();

        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }

        $session->set('cart', $cart);

        return $session->get('cart');
    }

    /**
     * Fonction qui permet de supprimer tous le panier
     *
     * @return self
     */
    public function deleteAll()
    {

        $session =  $this->request->getSession();

        $session->remove('cart');

        return $session->get('cart', []);
    }

    /**
     * Fonction qui permet de récupérer l'ensemble du panier
     *
     * @return array
     */
    public function getFull()
    {
        $session = $this->request->getSession();
        $data = $session->get('cart', []);
        $dataCart = [];
        if ($data) {
            foreach ($data as $id => $quantity) {
                $product = $this->entityManager->getRepository(Product::class)->findOneById($id);
                $dataCart[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                ];
            }
        }
        return $dataCart;
    }

    /**
     * Fonction qui récupére le montant total des articles
     *
     * @return integer
     */
    public function getTotal()
    {
        $total = 0;
        foreach ($this->getFull() as $element) {
            $total += ($element['product']->getPrice() * $element['quantity']) / 100;
        }
        return $total;
    }
    public function getQuantity()
    {
        $quantity = 0;
        foreach ($this->getFull() as $element) {
            $quantity += $element ['quantity'];
        }
        return $quantity;
    }

    public function getFullPaginated($page)
    {
        

        $session = $this->request->getSession();
        $data = $session->get('cart', []);
        $dataCart = [];
        if ($data) {
            foreach ($data as $id => $quantity) {
                $product = $this->entityManager->getRepository(Product::class)->findOneById($id);
                $dataCart[] = [
                    'product' => $product,
                    'quantity' => $quantity,                    
                ];                
            }
    }

    }
}

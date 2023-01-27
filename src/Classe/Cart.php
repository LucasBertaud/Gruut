<?php

namespace App\Classe;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Cart
{

public function __construct(private SessionInterface $session)
{
}

public function add($id, SessionInterface $session)
{
    $cart = $session->get("cart", []);
    
    if(!empty ($cart[$id])){
      $cart[$id]++  ;
    }
    else {
        $cart[$id] = 1 ;
    }

    $session->set("cart", $cart);

}
}
?>
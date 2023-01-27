<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/produit', name: 'product_')]
class ProductController extends AbstractController
{
     #[Route('/{slug}', name: 'show')]
    public function list(Product $product) 
    {
        return $this->render('product/show.html.twig', compact("product"));
    }
   
}

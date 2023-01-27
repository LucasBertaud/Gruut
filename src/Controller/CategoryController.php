<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Product;
use App\Repository\CategoriesRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/nos-categories', name: 'category_')]
class CategoryController extends AbstractController
{

    #[Route('/', name: 'index')]
    public function index (CategoriesRepository $categoriesRepository)
    {
        return $this->render('category/index.html.twig', [
            'categories' => $categoriesRepository->findAll()
        ]);
    }
    #[Route('/{slug}', name: 'list')]
    public function list (Categories $categories)
    {
        return $this->render('product/index.html.twig', compact("categories"));
    }
}
/*  $products = $listProduct->findAll();
        return $this->render('product/product.html.twig', compact("products")
    ); */
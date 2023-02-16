<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Product;
use App\Entity\Ratings;
use App\Form\QuantityType;
use App\Repository\CategoriesRepository;
use App\Repository\ProductRepository;
use App\Repository\RatingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/nos-categories', name: 'category_')]
class CategoryController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    #[Route('/', name: 'index')]
    public function index (CategoriesRepository $categoriesRepository)
    {
        return $this->render('category/index.html.twig', [
            'categories' => $categoriesRepository->findAll()
        ]);
    }
    #[Route('/{slug}', name: 'list')]
    public function list (Categories $categories, Request $request, SessionInterface $session, ProductRepository $products  )
    {
        
        $allRatings = [];
        $allNotes = [];
        foreach ($categories->getProducts() as $product) {
            array_push($allRatings, $product);
        }
          foreach($allRatings as $allRating){
            array_push($allNotes, $allRating->getNote());
          }
        // dd($allRatings[0]->getProduct());
        $session->remove('inputOfValue');
        $form = $this->createForm(QuantityType::class);
        $form->handleRequest($request);
        return $this->render('product/index.html.twig', [
            "allNotes" => $allNotes,
            "form" => $form->createView(),
            "categories" => $categories,
        ]);
    }
}
/*  $products = $listProduct->findAll();
        return $this->render('product/product.html.twig', compact("products")
    ); */
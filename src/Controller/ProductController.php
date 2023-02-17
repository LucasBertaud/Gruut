<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Ratings;
use App\Form\RatingsType;
use App\Repository\OrderDetailsRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\RatingsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/produit', name: 'product_')]
class ProductController extends AbstractController
{

    public function __construct(private EntityManagerInterface $entityManager)
    {
        
    }

     #[Route('/{slug}', name: 'show')]
    public function list(Product $product, UserInterface $user, Request $request, RatingsRepository $ratingsRepository, OrderRepository $orderRepository, OrderDetailsRepository $orderDetailsRepository) 
    {
        $productUserBuy = false;
        $commented = false;
        $rates = 0;
        $iteration = 0;
        $id = $product->getId();
        $slug = $product->getSlug();
        $allRatings = $ratingsRepository->findByProduct($id);
        // la boucle sert à connaître chaque notes de chaque utilisateur sur le produit pour plus tard faire la note général
        foreach($allRatings as $allRating){
            $iteration ++;
            $rates += $allRating->getRating();
            if($allRating->getUser() == $user){
                // commented permet de vérifier si l'utilisateur à commenté le produit
                $commented = true;
            }
        }
        $rateOfProduct = null;
        $generalRate = null;
        if($rates !== 0){
            // ici on initialise la variable de la note générale en prenant la moyenne des notes des utilisateurs en divisant par le nombres d'utilisateurs qui ont commentés.
            // le ceil permet d'arrondir
            $rateOfProduct = ceil(($rates / $iteration));
            $rateOfProduct = $product->setNote($rateOfProduct);
            $this->entityManager->persist($rateOfProduct);
            $this->entityManager->flush();
            $generalRate = $product->getNote();
        }
        $ratings = new Ratings;
        $user = $this->getUser();
        $id = $user->getId();
        $ordersByUser = $orderRepository->findByUserId($id);
        foreach ($ordersByUser as $orderByUser) {
            $id = $orderByUser->getId();
            $ordersDetails = $orderDetailsRepository->findByOrderId($id);
            foreach($ordersDetails as $orderDetail){
                if($orderDetail->getProduct() == $product->getName()){
                    // tout ça permet de voir si l'utilisateur a bien acheté le produit et il pourra commenter le produit
                    $productUserBuy = true;
                }
            }
        }
        $form = $this->createForm(RatingsType::class, $ratings);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $form->getData()->setUser($user);
            $form->getData()->setProduct($product);
            if($commented !== true){
                $this->entityManager->persist($ratings);
                $this->entityManager->flush();
               }
           return $this->redirectToRoute("product_show", [
                "slug" => $slug,
           ]);
        }

        return $this->render('product/show.html.twig', [
            "product" => $product,
            "user" => $user,
            "form" => $form->createView(),
            'allRatings' => $allRatings,
            'commented' => $commented,
            "productUserBuy" => $productUserBuy,
            "rateOfProduct" => $generalRate
        ]);
    }
   

    #[Route('/{slug}/{id}', name: 'show_modify')]
    public function modify(ProductRepository $product, Request $request, RatingsRepository $ratingsRepository, $id, $slug, UserInterface $user, OrderRepository $orderRepository, OrderDetailsRepository $orderDetailsRepository) 
    {
        $productUserBuy = false;
        $productFind = $product->findBySlug($slug);
        $commented = false;
        $rates = 0;
        $iteration = 0;
        $allRatings = $ratingsRepository->findByProduct($id);
        foreach($allRatings as $allRating){
            $iteration++;
            $rates += $allRating->getRating();
            if($allRating->getUser() == $user){
                $commented = true;
                break; 
            }
           }
           $rateOfProduct = null;
           if($rates !== 0){
        $rateOfProduct = $rates / $iteration;
    }
    $generalRate = $productFind[0]->getNote();
           $ordersByUser = $orderRepository->findByUserId($id);
        foreach ($ordersByUser as $orderByUser) {
            $id = $orderByUser->getId();
            $ordersDetails = $orderDetailsRepository->findByOrderId($id);
            foreach($ordersDetails as $orderDetail){
                if($orderDetail->getProduct() == $product->getName()){
                    $productUserBuy = true;
                }
            }
        }
        $rating = $ratingsRepository->findOneById($id);
        $formModify = $this->createForm(RatingsType::class, $rating);
        $formModify->handleRequest($request);
        if ($formModify->isSubmitted() && $formModify->isValid()) {
            $this->entityManager->persist($rating);
            $this->entityManager->flush();
            return $this->redirectToRoute("product_show", [
                "slug" => $slug,
           ]);
        }

        return $this->render("product/modify.html.twig", [
            "product" => $productFind[0],
            "slug" => $slug,
            "user" => $user,
            'commented' => $commented,
            "formModify" => $formModify->createView(),
            "productUserBuy" => $productUserBuy,
            'allRatings' => $allRatings,
            "rateOfProduct" => $generalRate
       ]);
    }

}

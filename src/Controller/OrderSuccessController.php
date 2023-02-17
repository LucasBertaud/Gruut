<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Address;
use App\Entity\Product;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address as MimeAddress;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{

    private $entityManager; 

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/commande/merci/{stripeSessionId}', name: 'app_order_validate')]
    public function index(OrderRepository $orderRepository,
     $stripeSessionId,
     ProductRepository $productRepository, 
     EntityManagerInterface $entityManager,  
     Cart $cart,
     MailerInterface $mailer,
     \Knp\Snappy\Pdf $knpSnappyPdf,
     ): Response
    {
        $date = new DateTimeImmutable();
        $datePDF = date('d-m-y');
        $user = $this->getUser();
        $order = $orderRepository->findOneByStripeSessionId($stripeSessionId);
        $id = $user->getId();
        $orderId = $order->getId();
        $orderDelivery = $order->getDelivery();
        // Création d'un array à partir des <br>
        $deliveryArray = explode("<br>", $orderDelivery);      
        $productItems = $order->getOrderDetails()->getValues();
        $carrierPrice = $order->getCarrierPrice() / 100;
        $total = null;
        // la boucle sert à connaître le total de tous les produits récupérés 
        foreach ($productItems as $product) {
            $total += ($product->getPrice() * $product->getQuantity()) / 100;
        }
        $totalCarrier = $total + $carrierPrice;
        
        if(!$order || $order->getUser() != $this->getUser()){
            return $this->redirectToRoute('app_home');
                }

        foreach($order->getOrderDetails()->getValues() as $quantity){
            $productAll = $productRepository->findOneByName($quantity->getProduct());
            $stockFinal = $productAll->getStock() - $quantity->getQuantity();          
            $productAll->setStock($stockFinal);                        
            $entityManager->persist($productAll);            
        }

        if($order->getState() == 0){
            $cart->deleteAll();
            // Permet de passer le statut de la commande a payé
            $order->setState(1);
                 
            // envoie du mail après confirmation de paiement
            $email = (new TemplatedEmail())
            ->from(new MimeAddress('gruut.company1@gmail.com', 'Gruut'))
            ->to($user->getEmail())
            ->subject('Confirmation de commande')
            ->htmlTemplate('order_validate/email.html.twig')
            ->context(['order' => $orderRepository->findOneByStripeSessionId($stripeSessionId) ])
        ;

        $mailer->send($email);

        
            // pour créer le PDF, on lui passe la vue twig bill.html.twig et dans l'array on peut y intégrer des variables
        $html = $this->renderView('profile/bill.html.twig', array(
            'user'  => $user,
            'orderDelivery' => $deliveryArray,
            'products' => $productItems,
            'order' => $order,
            'date' => $datePDF,
            'total' => $total,
            'totalCarrier' => $totalCarrier,
        ));
        // sha1 = hashage, uniqid = génère un ID
        $namepdf = sha1(uniqid());
        $pathpdf = "assets/pdf/$namepdf";
        // ici on génère le PDF via la variable html et on lui passe son chemin via la variable pathpdf
        $knpSnappyPdf->generateFromHtml($html, $pathpdf);
        // ici on mets dans la BDD la date de la création du PDF et le PDF
        $order->setBill($pathpdf);
        $order->setBillingDate($date);
        $entityManager->flush();
        }
        
        return $this->render('order_validate/index.html.twig', compact('order'));
   
    }

}

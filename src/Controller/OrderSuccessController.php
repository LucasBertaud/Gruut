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
        $deliveryArray = explode("<br>", $orderDelivery);
        $productItems = $order->getOrderDetails()->getValues();
        $carrierPrice = $order->getCarrierPrice() / 100;
        $total = null;
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

        if(!$order->getisPaid()){
            $cart->deleteAll();
            $order->setisPaid(1);
                 
            
            $email = (new TemplatedEmail())
            ->from(new MimeAddress('gruut.company@gmail.com', 'Gruut'))
            ->to($user->getEmail())
            ->subject('Confirmation de commande')
            ->htmlTemplate('order_validate/email.html.twig')
            ->context(['order' => $orderRepository->findOneByStripeSessionId($stripeSessionId) ])
        ;

        $mailer->send($email);

        

        $html = $this->renderView('profile/bill.html.twig', array(
            'user'  => $user,
            'orderDelivery' => $deliveryArray,
            'products' => $productItems,
            'order' => $order,
            'date' => $datePDF,
            'total' => $total,
            'totalCarrier' => $totalCarrier,
        ));
        $namepdf = "facture_$orderId.pdf";
        $pathpdf = "assets/pdf/$namepdf";
        $knpSnappyPdf->generateFromHtml($html, $pathpdf);
        $order->setBill($pathpdf);
        $order->setBillingDate($date);
        $entityManager->flush();
        }
        
        return $this->render('order_validate/index.html.twig', compact('order'));
   
    }

}

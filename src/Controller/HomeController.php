<?php

namespace App\Controller;

use App\Repository\ComponentProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Entity\Product;
use App\Form\ContactType;
use App\Security\UserAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class HomeController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request, SessionInterface $session, UserAuthenticatorInterface $userAuthenticator, UserAuthenticator $authenticator): Response
    {
    
        $userData = $session->get('user_data');
        $fromEmail = $request->query->get('token');
        if($fromEmail != null){
            if($fromEmail == $userData->getToken()){
             $this->entityManager->persist($userData);
             $this->entityManager->flush();
            
             return $userAuthenticator->authenticateUser(
                $userData,
                $authenticator,
                $request
             );
            }
        }
        
        // $entityManager->persist($userData);
            // $entityManager->flush();
        $products = $this->entityManager->getRepository(Product::class)->findByIsBest(1);
        return $this->render('home/index.html.twig', ['products' => $products]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {

        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('flash', 'Votre email a bien été envoyé.');
            $mail_user = $form->get('Email')->getData();
            $name_user = $form->get('Name')->getData();
            $subject_user = $form->get('Subject')->getData();
            $message_user = $form->get('Message')->getData();


            $mail = (new Email())
                ->from(new Address($mail_user , $name_user))
                ->to('gruut.company1@gmail.com')
                ->Subject($subject_user)
                ->text($message_user);
                $mail->getHeaders()->addTextHeader('X-Transport', 'secondary');
                $mailer->send($mail);
        }

        return $this->render('home/contact.html.twig', ['form' => $form->createView()]);
    }
}

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
        // on récupère des données de session depuis le register controller
        $userData = $session->get('user_data');
        // si l'utilisateur a cliqué sur le lien dans l'email qu'il a reçu, alors la variable fromEmail contiendra le token.
        $fromEmail = $request->query->get('token');
        if($fromEmail != null){
            // ici on compare le token de l'utilisateur et le token récupèré dans l'email, si ils sont identiques, alors tu enregistres l'utilisateur.
            if($fromEmail == $userData->getToken()){
             $this->entityManager->persist($userData);
             $this->entityManager->flush();
            // les lignes en dessous permettent de se connecter automatiquement
             return $userAuthenticator->authenticateUser(
                $userData,
                $authenticator,
                $request
             );
            }
        }
            // ici on récupère les produits vedettes, pour les afficher dans la page d'accueil
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
            // on récupère les données tranmise du formulaire, pour les envoyer en mail
            $mail_user = $form->get('Email')->getData();
            $name_user = $form->get('Name')->getData();
            $subject_user = $form->get('Subject')->getData();
            $message_user = $form->get('Message')->getData();

            // ici on initialise un nouveau mail
            $mail = (new Email())
                ->from(new Address($mail_user , $name_user))
                ->to('gruut.company1@gmail.com')
                ->Subject($subject_user)
                ->text($message_user);
                // avec mailer, on peut modifier le DSN, donc le transporteur, ici plutôt que d'utiliser MailJet, on utilise MailTrap.
                // Utilisation de mailtrap, car sur MailJet on ne peut pas envoyer un mail avec l'email de l'utilisateur, car il n'est pas vérifiée.
                $mail->getHeaders()->addTextHeader('X-Transport', 'secondary');
                $mailer->send($mail);
        }

        return $this->render('home/contact.html.twig', ['form' => $form->createView()]);
    }
}

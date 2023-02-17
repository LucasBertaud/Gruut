<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordConnectedFormType;
use App\Form\ChangeProfileType;
use App\Form\PasswordVerificationType;
use App\Repository\AddressRepository;
use App\Repository\OrderDetailsRepository;
use App\Repository\OrderRepository;
use App\Repository\RatingsRepository;
use App\Repository\ResetPasswordRequestRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/profil', name: 'profile_')]
class ProfileController extends AbstractController
{
    use ResetPasswordControllerTrait;

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, private ResetPasswordHelperInterface $resetPasswordHelper,)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'index')]
    public function index(UserInterface $user, AddressRepository $addressRepository, Request $request, SessionInterface $session): Response
    {
        $passwordIsVerif = false;
        $user = $this->getUser();
        $id = $this->getUser()->getId();
        $address = $addressRepository->findByUserId($id);
        $form = $this->createForm(PasswordVerificationType::class);
        $form->handleRequest($request);
        // ici c'est pour télécharger les données utilisateurs, mais avant on demande un passwordVerif
        if ($form->isSubmitted()) {
            $passwordverif = $form->get('passwordverif')->getData();
            if ($form->isSubmitted() && password_verify($passwordverif, $user->getPassword())) {
                if ($form->isSubmitted() && $form->isValid()) {
                    $passwordIsVerif = true;
                    return $this->render('profile/index.html.twig', [
                        "user" => $user,
                        "passwordIsVerif" => $passwordIsVerif,
                        'id' => $id,
                        'address' => $address,
                    ]);
                }
            }
        }
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'id' => $id,
            'address' => $address,
            'passwordVerif' => $form->createView(),
            "passwordIsVerif" => $passwordIsVerif
        ]); 
    }

     #[Route('/vos-données/télécharger/JSON', name: 'data_JSON_download')]
    public function data_JSON_download(Request $request, UserInterface $user, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $id = $user->getId();
        $data = $userRepository->findById($id)[0];
        // on créait un sérializer qui permet d'encoder en JSON
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        // ici on lui demande de sérializer notre data en JSON
        $jsonData = $serializer->serialize($data, 'json', [
            // on supprime certains attributs qui peuvent poser problèmes, par exemple : les clefs étrangères posent des problèmes de boucles("a circular reference has been detected")
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['roles', 'fullName' ,'token' ,'ratings' , 'addresses', 'orders', 'password', 'id', 'userIdentifier']
        ]);
        $dataJson = json_decode($jsonData);

        // ici on va créer notre réponse en  JSON, on lui passe json_pretty_print pour que ça soit "propre"
        $response = new JsonResponse(json_encode($dataJson, JSON_PRETTY_PRINT));
        $response->headers->set('Content-Type', 'application/json');
        // ici on peut choisir le nom du fichier.
        $response->headers->set('Content-Disposition', 'attachment;filename="user_' . $id . '.json"');
        return $response;
    }

    #[Route('/vos-données/télécharger/YAML', name: 'data_YAML_download')]
    public function data_YAML_download(Request $request, UserInterface $user, UserRepository $userRepository): Response
    {
         // se référer aux commentaires JSON
        $user = $this->getUser();
        $id = $user->getId();
        $data = $userRepository->findById($id)[0];

        $serializer = new Serializer([new ObjectNormalizer()], [new YamlEncoder()]);
        $yamlData = $serializer->serialize($data, 'yaml', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['roles', 'fullName' ,'token' ,'ratings' , 'addresses', 'orders', 'password', 'id', 'userIdentifier']
        ]);

        $response = new Response($yamlData);
        $response->headers->set('Content-Type', 'application/yaml');
        $response->headers->set('Content-Disposition', 'attachment;filename="user_' . $id . '.yaml"');

        return $response;
    }

    #[Route('/suppression-de-son-compte', name: 'delete_account')]
    public function delete_account(Request $request, UserInterface $user, UserRepository $userRepository, MailerInterface $mailer, UserPasswordHasherInterface $passwordHasher): Response
    {
        $alert = null;
        $exitDelay = null;
        $secondForm = $this->createForm(ChangePasswordConnectedFormType::class);
        $secondForm->handleRequest($request);
        $user = $this->getUser();
        $passwordIsVerif = false;
        $id = $user->getId();
        $data = $userRepository->findById($id)[0];
        $form = $this->createForm(PasswordVerificationType::class);
        $form->handleRequest($request);
        // $form = passwordverification pour la suppression de compte, tandis que $secondForm = ChangePassword pour changer le mdp
        if ($form->isSubmitted()) {
            
            $passwordverif = $form->get('passwordverif')->getData();
            if ($form->isSubmitted() && password_verify($passwordverif, $user->getPassword())) {
                if ($form->isSubmitted() && $form->isValid()) {
                    $passwordIsVerif = true;
                    // si il n'a pas dans session l'email envoyé, alors l'utilisateur peut reçevoir un mail
                    if (!$request->getSession()->get('email_sent_')) {
                    $token = bin2hex(random_bytes(32));
                    $user->setToken($token);
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    $email = (new TemplatedEmail())
                        ->from(new Address('gruut.company1@gmail.com', 'Gruut'))
                        ->to($user->getEmail())
                        ->subject('Suppression de compte')
                        ->htmlTemplate('profile/email.html.twig')
                        ->context(["token" => $token]);


                    $mailer->send($email);
                    $request->getSession()->set('email_sent_' , true);
                    return $this->render('profile/delete.html.twig', [
                        "user" => $user,
                        "passwordIsVerif" => $passwordIsVerif,
                        "alert" => $alert,
                        'resetForm' => $secondForm->createView(),
                        'exitDelay' => $exitDelay,
                    ]);
                }
                else{
                        return $this->render('profile/delete.html.twig', [
                            "user" => $user,
                            "passwordIsVerif" => $passwordIsVerif,
                            "alert" => $alert,
                            'resetForm' => $secondForm->createView(),
                            'exitDelay' => $exitDelay,
                        ]);
                }
            }
            }
        }
        // si tu passes par "essayer de nouveau" ou par le lien "supprimer son compte" alors on supprime dans la session "email_sent" pour pouvoir renvoyer un nouveau mail
        if($request->getRequestUri() == "/profil/suppression-de-son-compte?resetSession=1"){
           $request->getSession()->remove("email_sent_");
        }

            // ici c'est le formulaire modifier le mot de passe
        if ($secondForm->isSubmitted()) {

            $oldPassword = $secondForm->get('oldPassword')->getData();
            if ($secondForm->isSubmitted() && password_verify($oldPassword, $user->getPassword())) {
                if ($secondForm->isSubmitted() && $secondForm->isValid()) {
                    $alert = "Votre mot de passe a bien été modifié, vous allez être redirigé vers votre profil.";
                    $exitDelay = true;
                    $encodedPassword = $passwordHasher->hashPassword(
                        $user,
                        $secondForm->get('plainPassword')->getData()
                    );

                    $user->setPassword($encodedPassword);
                    $this->entityManager->flush();
                }
            } else if ($secondForm->isSubmitted() && !password_verify($oldPassword, $user->getPassword())) {
                $alert = "Erreur de confirmation de votre mot de passe actuel";
            }
        }
    

        return $this->render("profile/delete.html.twig", [
            "user" => $user,
            "passwordVerif" => $form->createView(), 
            "passwordIsVerif" => $passwordIsVerif,
            'resetForm' => $secondForm->createView(),
            'alert' => $alert,
            'exitDelay' => $exitDelay

            ]);
    }
    #[Route('/suppression-de-son-compte/supprimer', name: 'delete_account_erase')]
    public function delete_account_erase(Request $request, SessionInterface $session, TokenStorageInterface $tokenStorage, UserInterface $user, UserRepository $userRepository, AddressRepository $addressRepository, EntityManagerInterface $entity, ResetPasswordRequestRepository $resetrepo, RatingsRepository $ratingsRepository): Response
    {

        $user = $this->getUser();
        $fromEmail = $request->query->get('token');
        if ($fromEmail != null) {
        if ($fromEmail == $user->getToken()) {
            
            // supprime toutes les données de la BDD correspond à l'utilisateur
            $id = $user->getId();
            $resetpass = $resetrepo->findByUserId($id);
            foreach ($resetpass as $reset) {
                $resetrepo->delete($reset);
            }
            $address = $addressRepository->findByUserId($id);
            foreach ($address as $test) {
                $addressRepository->delete($test);
            }
            $ratings = $ratingsRepository->findByUserId($id);
            foreach ($ratings as $test) {
                $ratingsRepository->delete($test);
            }
            $entity->remove($user);
            $entity->flush();
            $tokenStorage->setToken(null);
            $user->setToken(null);
            // dégage toutes les SESSIONS
            $session->invalidate();

            return $this->redirectToRoute("app_logout");
        }

        else {
            return $this->redirectToRoute("profile_index");
        }
        }
        else {
            return $this->redirectToRoute("profile_index");
        }
    }

    #[Route('/historique-des-commandes', name: 'order_follow')]
    public function order_follow(Request $request, UserInterface $user, UserRepository $userRepository, OrderRepository $orderRepository, OrderDetailsRepository $orderDetailsRepository): Response
    {
        $user = $this->getUser();
        $id = $user->getId();
        $order = $orderRepository->findByUserId($id);
        $orderid = [];
        $orderDetails = [];
        // ici on boucle sur les commandes, pour à la fin récupérer chacun des orderDetails.
        if(!$order == []){
            foreach($order as $singleorder){
                $ordersid[] = $singleorder->getid();
            } 
            foreach($ordersid as $orderid){
                $orderDetails[] = $orderDetailsRepository->findByOrderId($orderid);
            }
            $orderDetails = array_reverse($orderDetails);
        }
        $random = random_bytes(10);
        $bill = [];
        // ici on récupère les factures
        foreach ($order as $orderBill) {
            $bill[] = [$orderBill->getBill(), $orderBill->getId()];
        }

        $bytes = (bin2hex($random));

        return $this->render("profile/order_follow.html.twig", [
        "user" => $user, 
        "orderDetails" =>$orderDetails, 
        'bills' => $bill, 
        'random' => $bytes
    ]);
    }
}

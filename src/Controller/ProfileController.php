<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordConnectedFormType;
use App\Form\ChangeProfileType;
use App\Form\PasswordVerificationType;
use App\Repository\AddressRepository;
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
    public function index(UserInterface $user): Response
    {
        $user = $this->getUser();
        $id = $this->getUser()->getId();
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'id' => $id
        ]);
    }

    #[Route('/modifier-le-mot-de-passe/{id}', name: 'change_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $exitDelay = null;
        $alert = null;
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordConnectedFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $oldPassword = $form->get('oldPassword')->getData();
            if ($form->isSubmitted() && password_verify($oldPassword, $user->getPassword())) {
                if ($form->isSubmitted() && $form->isValid()) {
                    $alert = "Votre mot de passe a bien été modifié, vous allez être redirigé vers votre profil.";
                    $exitDelay = true;
                    $encodedPassword = $passwordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    );

                    $user->setPassword($encodedPassword);
                    $this->entityManager->flush();
                }
            } else if ($form->isSubmitted() && !password_verify($oldPassword, $user->getPassword())) {
                $alert = "Erreur de confirmation de votre mot de passe actuel";
            }
        }



        return $this->render('profile/change_pass.html.twig', [
            'resetForm' => $form->createView(),
            'user' => $user,
            'alert' => $alert,
            'exitDelay' => $exitDelay,
            'user' => $user

        ]);
    }

    #[Route('/modifier-le-profil/{id}', name: 'modify')]
    public function modify(Request $request, $id, UserInterface $user): Response
    {
        $alert = null;
        $exitDelay = null;
        $user = $this->getUser();
        $id = $this->getUser()->getId();
        $form = $this->createForm(ChangeProfileType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $alert = "Votre profil a bien été modifié, vous allez être redirigé vers votre profil.";
            $exitDelay = true;
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
        return $this->render('profile/change_profile.html.twig', [
            'profileForm' => $form->createView(),
            'id' => $id,
            'alert' => $alert,
            'exitDelay' => $exitDelay,
            'user' => $user
        ]);
    }
    #[Route('/vos-donnees/', name: 'data')]
    public function data(Request $request, UserInterface $user): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(PasswordVerificationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $passwordverif = $form->get('passwordverif')->getData();
            if ($form->isSubmitted() && password_verify($passwordverif, $user->getPassword())) {
                if ($form->isSubmitted() && $form->isValid()) { 
                    return $this->render('profile/data2.html.twig', [
                        "user" => $user,
                    ]);
                }
            }
        }
        return $this->render('profile/data.html.twig', [
            "user" => $user,
            'passwordVerif' => $form->createView(),
        ]);
    }

    #[Route('/vos-données/télécharger/JSON', name: 'data_JSON_download')]
    public function data_JSON_download(Request $request, UserInterface $user, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $id = $user->getId();
        $data = $userRepository->findById($id)[0];

        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $jsonData = $serializer->serialize($data, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['roles', 'addresses', 'orders', 'password', 'id', 'userIdentifier']
        ]);
        $dataJson = json_decode($jsonData);


        $response = new JsonResponse(json_encode($dataJson, JSON_PRETTY_PRINT));
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Content-Disposition', 'attachment;filename="user_' . $id . '.json"');

        return $response;
    }

    #[Route('/vos-données/télécharger/YAML', name: 'data_YAML_download')]
    public function data_YAML_download(Request $request, UserInterface $user, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $id = $user->getId();
        $data = $userRepository->findById($id)[0];

        $serializer = new Serializer([new ObjectNormalizer()], [new YamlEncoder()]);
        $yamlData = $serializer->serialize($data, 'yaml', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['roles', 'addresses', 'orders', 'password', 'id', 'userIdentifier']
        ]);
       
        $response = new Response($yamlData);
        $response->headers->set('Content-Type', 'application/yaml');
        $response->headers->set('Content-Disposition', 'attachment;filename="user_' . $id . '.yaml"');

        return $response;
    }

    #[Route('/suppression-de-son-compte', name: 'delete_account')]
    public function delete_account(Request $request, UserInterface $user, UserRepository $userRepository, MailerInterface $mailer): Response
    {
        $user = $this->getUser();
        $id = $user->getId();
        $data = $userRepository->findById($id)[0];
        $form = $this->createForm(PasswordVerificationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $passwordverif = $form->get('passwordverif')->getData();
            if ($form->isSubmitted() && password_verify($passwordverif, $user->getPassword())) {
                if ($form->isSubmitted() && $form->isValid()) { 

                    $email = (new TemplatedEmail())
                ->from(new Address('gruut.company@gmail.com', 'Gruut'))
                ->to($user->getEmail())
                ->subject('Suppression de compte')
                ->htmlTemplate('profile/email.html.twig')
            ;
    
            $mailer->send($email);

                    return $this->render('profile/delete2.html.twig', [
                        "user" => $user,
                    ]);
                }
            }
        }
        return $this->render("profile/delete.html.twig", ["user" => $user, "passwordVerif" => $form->createView(),]);
    }
    #[Route('/suppression-de-son-compte/supprimer', name: 'delete_account_erase')]
    public function delete_account_erase(Request $request, SessionInterface $session , TokenStorageInterface $tokenStorage , UserInterface $user, UserRepository $userRepository, AddressRepository $addressRepository, EntityManagerInterface $entity, ResetPasswordRequestRepository $resetrepo): Response
    {
        $user = $this->getUser();
        $id = $user->getId(); 
        $resetpass = $resetrepo->findByUserId($id);
        foreach ($resetpass as $reset){
            $resetrepo->delete($reset);
        }
        $address = $addressRepository->findByUserId($id);
        foreach ($address as $test){
            $addressRepository->delete($test);
        }
        $entity->remove($user);
        $entity->flush();

        $tokenStorage->setToken(null);
        $session->invalidate();

        return $this->redirectToRoute("app_logout" );
    }

    #[Route('/commandes-et-suivis', name: 'order_follow')]
    public function order_follow(Request $request, UserInterface $user, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $id = $user->getId();
        $data = $userRepository->findById($id)[0];

        return $this->render("profile/order_follow.html.twig", ["user" => $user]);
    }

    #[Route('/facture', name: 'bill')]
    public function bill(Request $request, UserInterface $user, UserRepository $userRepository, \Knp\Snappy\Pdf $knpSnappyPdf): Response
    {
        $user = $this->getUser();
        $id = $user->getId();
        $data = $userRepository->findById($id)[0];


        $html = $this->renderView('profile/order_follow.html.twig', array(
            'user'  => $user
        ));

        return new PdfResponse(
            $knpSnappyPdf->getOutputFromHtml($html),
            'facture.pdf'
        );
    }
}

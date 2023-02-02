<?php

namespace App\Controller;

use App\Form\ChangePasswordFormType;
use App\Form\ChangeProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
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
        $form = $this->createForm(ChangePasswordFormType::class);
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
            }  
            else if ($form->isSubmitted() && !password_verify($oldPassword, $user->getPassword())) {
                  $alert = "Erreur de confirmation de votre mot de passe actuel";
            }
        } 

        

        return $this->render('profile/change_pass.html.twig', [
            'resetForm' => $form->createView(),
            'user' => $user,
            'alert' => $alert,
            'exitDelay' => $exitDelay

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
            'exitDelay' => $exitDelay
        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressType;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/adresse', name: 'address_')]
class AddressController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
    
        $this->entityManager = $entityManager;
    }

    // Création du formulaire d'adresse
    #[Route('/formulaire', name: 'index')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        
        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // stockage de l'utilisateur dans le champ user_id
            $address->setUserId($this->getUser());            
            $this->entityManager->persist($address);
            $this->entityManager->flush();
            return $this->redirectToRoute('profile_index');
        }
        return $this->render('address/index.html.twig', ['addressForm' => $form->createView(), 'user' => $user]);
    }

    #[Route('/recap', name: 'recap')]
    public function recap(UserInterface $user, AddressRepository $addressRepository): Response
    {
        $user = $this->getUser();
        $ID = $this->getUser()->getId();
        $address = $addressRepository->findBy(['user_id' => $ID]);
        return $this->render('address/recap.html.twig', [
            'user' => $user,
            'address' => $address,
        ]);
    }

    #[Route('/modifier/{id}', name: 'modify')]
    public function modify(AddressRepository $addressRepository, $id, Request $request): Response
    {
        $user = $this->getUser();
        // récupération des données de l'adresse de l'utilisateur 
        $address = $addressRepository->findOneById($id);
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($address);
            $this->entityManager->flush();
            return $this->redirectToRoute('profile_index');
        }
        return $this->render('address/index.html.twig', ['addressForm' => $form->createView(), 'user' => $user]);
    }

    #[Route('/supprimer/{id}', name: 'delete')]
    public function delete(AddressRepository $addressRepository, $id): Response
    {
        $address = $addressRepository->findOneById($id);
        if ($address && $address->getUserId() == $this->getUser()) {
            $this->entityManager->remove($address);
            $this->entityManager->flush();
        }
        return $this->redirectToRoute('profile_index');
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Carrier;
use App\Entity\Categories;
use App\Entity\Component;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
          return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Gruut');
    }

    public function configureMenuItems(): iterable
    {    
         yield MenuItem::linkToCrud('Utilisateur', 'fas fa-user', User::class);
         yield MenuItem::linkToCrud('Catégories', 'fas fa-list', Categories::class);
         yield MenuItem::linkToCrud('Produits', 'fas fa-tag', Product::class);
         yield MenuItem::linkToCrud('Transporteur', 'fas fa-truck', Carrier::class);
         yield MenuItem::linkToCrud('Commande', 'fas fa-bag-shopping', Order::class);
         yield MenuItem::linkToCrud('Composant', 'fas fa-screwdriver', Component::class);
    }

}

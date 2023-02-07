<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Repository\ProductRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->renderContentMaximized()
        ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $commandeComposants = Action::new('commandeComposants','Composants pour la commande')->linkToCrudAction('commandeComposants');

        return $actions
            ->add('detail',$commandeComposants)
            ->add('index','detail')
            ->remove('index', Action::NEW)
            ->remove('index', Action::EDIT)
            ->remove('index', Action::DELETE)
            ->remove('detail', Action::EDIT)
            ->remove('detail', Action::DELETE);
    }

    public function commandeComposants(AdminContext $admin,ProductRepository $productRepository){
        $order = $admin->getEntity()->getInstance();
        $productOrder = [];
       
        foreach($order->getOrderDetails()->getValues() as $product){            
        $productOrder += [            
             $product->getProduct() => $product->getQuantity()
            ];              
        } 
     
      $componantsProducts = [];
      foreach($productOrder as $nameProduct => $item){
        $product = $productRepository->findOneByName($nameProduct);   
        
         $componantsProducts += [
             $item => ($product->getComponants()->getValues())
        ];
          
      }

      
      
      
      
      return $this->render('admin/composantsCommande.html.twig',compact('productOrder','order','componantsProducts'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            DateField::new('created_at', 'Commande du '),
            TextField::new('user.getFullName', 'Utilisateur'),
            TextField::new('reference', 'Référence'),
            MoneyField::new('getTotalProduct','Montant')->setCurrency('EUR'),
            TextField::new('carrierName','Transporteur'),
            MoneyField::new('carrierPrice','Coût transport')->setCurrency('EUR'),
            BooleanField::new('isPaid', 'Payée'),
            ArrayField::new('getOrderDetails', 'Produits')->hideOnIndex()
        ];
    }
    
}

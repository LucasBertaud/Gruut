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
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;


class OrderCrudController extends AbstractCrudController
{

    private $entityManager;
    private $adminUrlGenerator;

    public function __construct(EntityManagerInterface $entityManager,AdminUrlGenerator $adminUrlGenerator)
        {
            $this->entityManager = $entityManager;
            $this->adminUrlGenerator = $adminUrlGenerator;
        }

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
        $updatePreparation = Action::new('updatePreparation','Préparation en cours', 'fas fa-box-open')->linkToCrudAction('updatePreparation');
        $updateDelivery = Action::new('updateDelivery','Livraison en cours', 'fas fa-truck')->linkToCrudAction('updateDelivery');

        return $actions
        ->add('detail',$commandeComposants)
        ->add('index','detail')
        ->add('detail', $updatePreparation)
        ->add('detail', $updateDelivery)
            ->remove('index', Action::NEW)
            ->remove('index', Action::EDIT)
            ->remove('index', Action::DELETE)
            ->remove('detail', Action::EDIT)
            ->remove('detail', Action::DELETE);
    }

    public function updatePreparation(AdminContext $context)
    {
       $order = $context->getEntity()->getInstance();
       $order->setState(2);
       $this->entityManager->flush();

       $url = $this->adminUrlGenerator
        ->setController(OrderCrudController::class)
        ->setAction('index')
        ->generateUrl();

    return $this->redirect($url);
    }

    public function updateDelivery(AdminContext $context)
    {
       $order = $context->getEntity()->getInstance();
       $order->setState(3);
       $this->entityManager->flush();

       $url = $this->adminUrlGenerator
        ->setController(OrderCrudController::class)
        ->setAction('index')
        ->generateUrl();

    return $this->redirect($url);
    }

   public function commandeComposants(AdminContext $admin,ProductRepository $productRepository, Request $request){
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

         $componantsProducts [] =  [
             $item => $product->getComponants()->getValues()];         
        }
        $referer = $request->headers->get('referer');    
      return $this->render('admin/composantsCommande.html.twig',compact('productOrder','order','referer','componantsProducts'));
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            DateField::new('created_at', 'Commande du '),
            TextField::new('user.getFullName', 'Utilisateur'),
            TextEditorField::new('delivery', 'Adresse de livraison')->onlyOnDetail(),
            TextField::new('reference', 'Référence'),
            MoneyField::new('getTotalProduct','Montant')->setCurrency('EUR'),
            TextField::new('carrierName','Transporteur'),
            MoneyField::new('carrierPrice','Coût transport')->setCurrency('EUR'),
            ChoiceField::new('state')->setChoices([
                'Non payée' => 0,
                'Payée' => 1, 
                'Préparation en cours' => 2, 
                'Livraison en cours' =>3
            ]),
            ArrayField::new('getOrderDetails', 'Produits')->hideOnIndex(),
            DateField::new('billing_date', 'Facture du '),
            UrlField::new('bill', 'facture')
        ];
    }
    
}

<?php

namespace App\Controller\Admin;

use App\Entity\Componants;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ComponantsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Componants::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            
            TextField::new('name'),
            IntegerField::new('quantity'),
            AssociationField::new('product'),
        ];
    }
    
}

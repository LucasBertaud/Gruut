<?php

namespace App\Controller\Admin;

use App\Entity\ComponentProduct;
use Doctrine\ORM\Query\AST\QuantifiedExpression;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class ComponentProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ComponentProduct::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('product'),
            AssociationField::new('component'),
            IntegerField::new('quantity')
        ];
    }
    
}

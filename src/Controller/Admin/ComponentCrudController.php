<?php

namespace App\Controller\Admin;

use App\Entity\Component;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ComponentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Component::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}

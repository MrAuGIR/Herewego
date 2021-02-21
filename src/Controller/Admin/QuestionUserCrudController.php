<?php

namespace App\Controller\Admin;

use App\Entity\QuestionUser;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class QuestionUserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return QuestionUser::class;
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

<?php

namespace App\Controller\Admin;

use App\Entity\QuestionAdmin;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
// les namespace suivants sont utilisé pour personnalisé l'affichage des données
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class QuestionAdminCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return QuestionAdmin::class;
    }

    /**
     * Dé-commenter cette méthode si on veut pouvoir personnaliser l'affichage des données
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('question'),
            TextEditorField::new('answer'),
            IntegerField::new('importance')
        ];
    }
    
}

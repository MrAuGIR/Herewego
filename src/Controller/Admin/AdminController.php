<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\City;
use App\Entity\Event;
use App\Entity\EventGroup;
use App\Entity\QuestionUser;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\QuestionAdmin;

class AdminController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Herewego');
    }

    public function configureMenuItems(): iterable
    {
        /*
        * Ici on rajoute les items du menu de gauche 
        */
        return [ MenuItem::linktoDashboard('Dashboard', 'fa fa-home'),
            MenuItem::section('Users'),
            MenuItem::linkToCrud('Users', 'fa fa-user', User::class),
            MenuItem::linkToCrud('Villes','fa fa-city', City::class),
            MenuItem::section('Event'),
            MenuItem::linkToCrud('Event', 'fas fa-calendar-week', Event::class),
            MenuItem::linkToCrud('Catégorie', 'fas fa-layer-group', Category::class),   
            MenuItem::linkToCrud('Groupe event', 'fas fa-object-group', EventGroup::class),
            MenuItem::section('FAQ'),
            MenuItem::linkToCrud('Question utilisateurs', 'fa fa-question', QuestionUser::class),
            MenuItem::linkToCrud('Question Administrateur', 'fa fa-reply', QuestionAdmin::class),
            MenuItem::section('Action'),
            MenuItem::linkToLogout('Logout', 'fa fa-exit'),
            
        ];
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}

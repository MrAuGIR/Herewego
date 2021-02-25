<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user/profil", name="user_profil")
     */
    public function profil()
    {
        return $this->render('user/profil.html.twig');
    }
    
    /**
     * @Route("/user/profil/edit", name="user_edit")
     */
    public function edit()
    {
        return $this->render('user/edit.html.twig');
    }

    /**
     * @Route("/user/profil/delete", name="user_delete")
     */
    public function delete()
    {
        dd("traitement du delete d'un user");
        // return $this->render('user/profil.html.twig');
    }
    
    /**
     * @Route("/user/events", name="user_events")
     */
    public function events()
    {
        return $this->render('user/events.html.twig');
    }

    /**
     * @Route("/user/history", name="user_history")
     */
    public function history()
    {
        return $this->render('user/history.html.twig');
    }


}

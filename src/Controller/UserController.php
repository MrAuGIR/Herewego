<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    /**
     * @Route("/user/profil", name="user_profil")
     */
    public function profil(Security $security)
    {
        $user = $security->getUser();

        return $this->render('user/profil.html.twig', [
            'user' => $user
        ]);
    }
    
    /**
     * @Route("/user/profil/edit", name="user_edit")
     */
    public function edit(Security $security)
    {

        $user = $security->getUser();

        return $this->render('user/edit.html.twig', [
            'user' => $user
        ]);
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
    public function events(Security $security)
    {
        $user = $security->getUser();


        return $this->render('user/events.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/user/history", name="user_history")
     */
    public function history(Security $security)
    {
        $user = $security->getUser();

        return $this->render('user/history.html.twig', [
            'user' => $user
        ]);
    }


}

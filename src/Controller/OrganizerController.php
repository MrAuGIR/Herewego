<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class OrganizerController extends AbstractController
{
    /**
     * @Route("/organizer/profil", name="organizer_profil")
     */
    public function profil()
    {
        return $this->render('organizer/profil.html.twig');
    }
    
    /**
     * @Route("/organizer/profil/update", name="organizer_update")
     */
    public function update()
    {
        return $this->render('organizer/update.html.twig');
    }

    /**
     * @Route("/organizer/profil/delete", name="organizer_delete")
     */
    public function delete()
    {
        dd("traitement du delete d'un organizer");
        // return $this->render('organizer/profil.html.twig');
    }
    
    /**
     * @Route("/organizer/events", name="organizer_events")
     */
    public function events()
    {
        return $this->render('organizer/events.html.twig');
    }

    /**
     * @Route("/organizer/history", name="organizer_history")
     */
    public function history()
    {
        return $this->render('organizer/history.html.twig');
    }

    /**
     * @Route("/organizer/stats", name="organizer_stats")
     */
    public function stats()
    {
        return $this->render('organizer/stats.html.twig');
    }

}

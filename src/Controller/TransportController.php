<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TransportController extends AbstractController
{
    /**
     * @Route("/transport/{event_slug}", name="transport")
     */
    public function index($event_slug)
    {
        return $this->render('transport/index.html.twig', [
            'eventSlug' => $event_slug,
        ]);
    }


    /**
     * @Route("/transport/{event_slug}/{transport_id}", name="transport_show")
     */
    public function show($event_slug, $transport_id)
    {
        return $this->render('transport/show.html.twig', [
            'eventSlug' => $event_slug,
            'transportId' => $transport_id
        ]);
    }
    /**
     * @Route("/manager/{transport_id}", name="transport_manage")
     */
    public function manage($transport_id)
    {
        return $this->render('transport/manage.html.twig', [
            'transportId' => $transport_id,
        ]);
    }
    /**
     * @Route("/manager/create/{event_slug}", name="transport_create")
     */
    public function create($event_slug)
    {
        return $this->render('transport/create.html.twig', [
            'eventSlug' => $event_slug,
        ]);
    }
    /**
     * @Route("/manager/update/{transport_id}", name="transport_update")
     */
    public function update($transport_id)
    {
        return $this->render('transport/update.html.twig', [
            'transportId' => $transport_id,
        ]);
    }


    /**
     * @Route("/manager/delete/{transport_id}", name="transport_delete")
     */
    public function delete($transport_id)
    {

        // gestion de la suppression d'un transport

        // redirection 

        dd($transport_id);

        // return $this->render('transport/delete.html.twig');
    }
}

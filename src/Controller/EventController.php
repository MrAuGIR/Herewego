<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    /**
     * @Route("/event", name="event")
     */
    public function index()
    {
        return $this->render('event/index.html.twig');
    }

    /**
     * @Route("/event/{category_slug}/{event_slug}", name="event_show")
     */
    public function show($category_slug, $event_slug)
    {
        return $this->render('event/show.html.twig', [
            'categorySlug' => $category_slug,
            'eventSlug' => $event_slug,
        ]);
    }

    /**
     * @Route("/event/{category_slug}", name="event_category")
     */
    public function category($category_slug)
    {
        return $this->render('event/category.html.twig', [
            'categorySlug' => $category_slug
        ]);
    }

    /**
     * @Route("/organizer/create", name="event_create")
     */
    public function create()
    {
        // formualire de creation de l'event

        // lorsque le formulaire est soumis, ou est ce redirigé ? à voir ?

        return $this->render('event/create.html.twig');
    }

    /**
     * @Route("/organizer/update/{id}", name="event_update")
     */
    public function update($id)
    {
        // formulaire pour update l'event avec l'{id}

        // lorsque le formulaire est soumis, ou est ce redirigé ? à voir ?

        return $this->render('event/update.html.twig', [
            'id' => $id
        ]);
    }

    /**
     * @Route("/organizer/delete/{id}", name="event_delete")
     */
    public function delete($id)
    {
        

        dd($id);
        // traitement de la suppression

        // redirection

        // return $this->render('event/delete.html.twig');
    }
}

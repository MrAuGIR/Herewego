<?php

namespace App\Controller;

use DateTime;
use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventController extends AbstractController
{
    /**
     * @Route("/event", name="event")
     */
    public function index(EventRepository $eventRepository)
    {
        $events = $eventRepository->findAll();

        dump($events);

        return $this->render('event/index.html.twig', [
            'events' => $events
        ]);
    }

    /**
     * @Route("/event/{category_slug}/{event_slug}", name="event_show")
     */
    public function show($event_slug, EventRepository $eventRepository)
    {
        $event = $eventRepository->findOneBy([
            'slug' => $event_slug
        ]);

        if (!$event) {
            throw $this->createNotFoundException("l'event demandé n'existe pas!");
        }

        dump($event);

        return $this->render('event/show.html.twig', [
            'event' => $event
        ]);
    }

    /**
     * @Route("/event/{category_slug}", name="event_category")
     */
    public function category($category_slug, CategoryRepository $categoryRepository)
    {
        $category = $categoryRepository->findOneBy([
            'slug' => $category_slug
        ]);

        if (!$category) {
            throw $this->createNotFoundException("la catégorie demandée n'existe pas!");
        }

        dump($category);

        return $this->render('event/category.html.twig', [
            'category' => $category
        ]);
    }

    /**
     * @Route("/organizer/create", name="event_create")
     */
    public function create(Request $request, SluggerInterface $slugger, EntityManagerInterface $em)
    {
        $event = new Event;

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $event->setSlug(strtolower($slugger->slug($event->getTitle())));
            $event->setTag(strtoupper($slugger->slug($event->getTitle())));
            $event->setCreatedAt(new DateTime());
            $em->persist($event);

            $em->flush();
            return $this->redirectToRoute('home');
        }

        $formView = $form->createView();

        return $this->render('event/create.html.twig', [
            'formView' => $formView
        ]);
    }

    /**
     * @Route("/organizer/update/{id}", name="event_update")
     */
    public function update($id, SluggerInterface $slugger, EventRepository $eventRepository, Request $request, EntityManagerInterface $em)
    {

        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException("l'event demandé n'existe pas!");
        }

        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $event->setSlug(strtolower($slugger->slug($event->getTitle())));
            $event->setTag(strtoupper($slugger->slug($event->getTitle())));

            $em->flush();

            return $this->redirectToRoute('home');
        }

        $formView = $form->createView();

        return $this->render('event/update.html.twig', [
            'formView' => $formView,
            'event' => $event

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

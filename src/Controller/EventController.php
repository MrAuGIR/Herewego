<?php

namespace App\Controller;

use DateTime;
use App\Entity\City;
use App\Entity\Event;
use App\Entity\Picture;
use App\Form\EventType;
use App\Entity\Localisation;
use App\Entity\Participation;
use App\Repository\EventRepository;
use App\Repository\CategoryRepository;
use App\Repository\EventGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParticipationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
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
     * @Route("/event/show/{event_id}", name="event_show")
     */
    public function show($event_id, EventRepository $eventRepository, EntityManagerInterface $em)
    {
        $event = $eventRepository->findOneBy([
            'id' => $event_id
        ]);
        
        if (!$event) {
            throw $this->createNotFoundException("l'event demandé n'existe pas!");
        }

        //faire countViews++ OK
        $event->setCountViews($event->getCountViews()+1);
        $em->flush();

        dump($event);

        return $this->render('event/show.html.twig', [
            'event' => $event
        ]);
    }

    /**
     * @Route("/event/category/{category_id}", name="event_category")
     */
    public function category($category_id, CategoryRepository $categoryRepository)
    {
        $category = $categoryRepository->find($category_id);

        if (!$category) {
            throw $this->createNotFoundException("la catégorie demandée n'existe pas!");
        }

        dump($category);

        return $this->render('event/category.html.twig', [
            'category' => $category
        ]);
    }

    /**
     * @Route("/event/group/{group_id}", name="event_group")
     */
    public function group($group_id, EventGroupRepository $eventGroupRepository)
    {
        $eventGroup = $eventGroupRepository->find($group_id);

        if (!$eventGroup) {
            throw $this->createNotFoundException("le groupe demandé n'existe pas!");
        }

        dump($eventGroup);

        return $this->render('event/group.html.twig', [
            'eventGroup' => $eventGroup
        ]);
    }

    /**
     * @Route("/event/participate/{event_id}", name="event_participate")
     */
    public function participate($event_id, EventRepository $eventRepository, Security $security, EntityManagerInterface $em)
    {
        // recuperer l'event
        $event = $eventRepository->find($event_id);

        // recuperer l'id du user connecté
        $user = $security->getUser();

        if (!$user) {
           // rediriger vers la page de connection
        }

        // rajouter une ligne dans la table participation        
        $participation = new Participation;
        $participation->setEvent($event)
            ->setUser($user)
            ->setAddedAt(new DateTime());

        // AVANT CA VERIFIER SI PARTICIPE PAS DEJA 
        // pour l'instant possible de s'inscrire plusieur fois au meme event
        
        $em->persist($participation);
        $em->flush();

        // rediriger vers la page de l'event (avec message success)
        return $this->redirectToRoute('event_show', [
            'event_id' => $event_id
        ]);
    }

    /**
     * @Route("/event/create", name="event_create")
     */
    public function create(Request $request, SluggerInterface $slugger, EntityManagerInterface $em, Security $security)
    {
        
        // verifier si c'est un ORGANIZER
        $event = new Event;

        $user = $security->getUser();

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // creation de la ville
            $cityName = $request->request->get('event')['cityName'];
            $cityCp = $request->request->get('event')['cityCp'];
            $city = new City();
            $city->setCityName($cityName)
                ->setCityCp($cityCp);
            $em->persist($city);
            
            //creation de la localisation (grace a city)
            $adress = $request->request->get('event')['adress'];
            $localisation = new Localisation();
            $localisation->setAdress($adress)
                ->setCity($city);
            $em->persist($localisation);

            //creation de l'event (grace a localisation)
            $event->setSlug(strtolower($slugger->slug($event->getTitle())))
                ->setTag(strtoupper($slugger->slug($event->getTitle())))
                ->setCreatedAt(new DateTime())
                ->setUser($user)
                ->setLocalisation($localisation);
            $em->persist($event);

            //creation de la picture (grace à l'event)
            $path = $request->request->get('event')['picturePath'];
            $title = $request->request->get('event')['pictureTitle'];
            $picture = new Picture();
            $picture->setPath($path)
                ->setTitle($title)
                ->setEvent($event)
                ->setOrderPriority(1);
            $em->persist($picture);

            $em->flush();
            return $this->redirectToRoute('home');
        }

        $formView = $form->createView();

        return $this->render('event/create.html.twig', [
            'formView' => $formView
        ]);
    }



    /**
     * @Route("/event/edit/{event_id}", name="event_edit")
     */
    public function edit($event_id, SluggerInterface $slugger, EventRepository $eventRepository, Request $request, EntityManagerInterface $em)
    {

        $event = $eventRepository->find($event_id);

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
     * @Route("/event/delete/{event_id}", name="event_delete")
     */
    public function delete($event_id, EventRepository $eventRepository, EntityManagerInterface $em)
    {
        // recuperer l'event_id passé en param
        $event = $eventRepository->find($event_id);

        if (!$event) {
            throw $this->createNotFoundException("l'event demandé n'existe pas!");
        }
        // traitement de la suppression
        $em->remove($event);
        $em->flush();
        // CA MARCHE MAIS J'AI PASSER EN CASCADE AU DELETE POUR TRANSPORT ET PICTURE
        // JE PENSE QUE C'EST LE COMPORTEMENT A FAIRE MAIS IL FAUT PREVENIR LES USERS QUE C'EST FAIT
        // MAIL A FAIRE JUSTE AVANT LE DELETE (MEME CHOSE POUR UPDATE)
        
        // redirection vers le dash organizer / events (avec message)
        return $this->redirectToRoute('event');
    }

}

<?php

namespace App\Controller;

use DateTime;
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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        return $this->render('event/index.html.twig', [
            'events' => $events
        ]);
    }

    /**
     * @Route("/event/show/{event_id}", name="event_show")
     */
    public function show($event_id, ParticipationRepository $participationRepository, EventRepository $eventRepository, EntityManagerInterface $em)
    {
        $event = $eventRepository->findOneBy([
            'id' => $event_id
        ]);
        if (!$event) {
            throw $this->createNotFoundException("l'event demandé n'existe pas!");
        }

        // recuperer l'id du user connecté // si pas connecté $user = Null
        $user = $this->getUser();

        // si user connecté, on regarde si il participe à l'event en cours
        $isOnEvent = false;
        if ($user) {
            $participations = $participationRepository->findBy([
                'user' => $user->getId(),
                'event' => $event->getId()
            ]);
            if (!empty($participations)) {
                $isOnEvent = true;
            }
        }        

        dump($isOnEvent, $user, $event);

        //faire countViews++
        $event->setCountViews($event->getCountViews()+1);
        $em->flush();

        return $this->render('event/show.html.twig', [
            'event' => $event,
            'user' => $user,
            'isOnEvent' => $isOnEvent
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

        return $this->render('event/group.html.twig', [
            'eventGroup' => $eventGroup
        ]);
    }

    /**
     * @Route("/event/participate/{event_id}", name="event_participate")
     */
    public function participate($event_id, EventRepository $eventRepository, EntityManagerInterface $em, ParticipationRepository $participationRepository)
    {
        // recuperer l'event
        $event = $eventRepository->find($event_id);
        if (!$event) {
            //! message flash
            return $this->redirectToRoute('event');
        }

        // recuperer l'id du user connecté
        $user = $this->getUser();
        if (!$user) {
            //! message flash
            return $this->redirectToRoute('app_login');
        }

        // Verifie si le user participe déjà à cet event
        $participations = $participationRepository->findBy([
            'user' => $user->getId(),
            'event' => $event->getId()
        ]);
        if (!empty($participations)) {
            //! message flash : participe deja
            return $this->redirectToRoute('event_show', [
                'event_id' => $event_id
            ]);
        }

        // rajouter une ligne dans la table participation        
        $participation = new Participation;
        $participation->setEvent($event)
            ->setUser($user)
            ->setAddedAt(new DateTime());

        
        $em->persist($participation);
        $em->flush();

        // rediriger vers la page de l'event (avec message success)
        return $this->redirectToRoute('event_show', [
            'event_id' => $event_id
        ]);
    }
    /**
     * @Route("/event/cancel/{event_id}", name="event_cancel")
     */
    public function cancel($event_id, EventRepository $eventRepository, EntityManagerInterface $em, ParticipationRepository $participationRepository)
    {
        // recuperer l'event
        $event = $eventRepository->find($event_id);
        if (!$event) {
            //! message flash
            return $this->redirectToRoute('event');
        }

        // recuperer l'id du user connecté
        $user = $this->getUser();
        if (!$user) {
            //! message flash
            return $this->redirectToRoute('app_login');
        }

        // si le user participe pas on le redirige
        $participation = $participationRepository->findOneBy([
            'user' => $user->getId(),
            'event' => $event->getId()
        ]);
        if (empty($participation)) {
            //! message flash : participe pas a cet event
            return $this->redirectToRoute('event_show', [
                'event_id' => $event_id
            ]);
        }

        // on supprime la participation     
        $em->remove($participation);
        $em->flush();

        // rediriger vers la page de l'event (avec message success)
        return $this->redirectToRoute('event_show', [
            'event_id' => $event_id
        ]);
    }

    /**
     * @Route("/event/create", name="event_create")
     */
    public function create(Request $request, SluggerInterface $slugger, EntityManagerInterface $em)
    {
        
        // verifier si c'est un ORGANIZER
        $user = $this->getUser();
        if (!$user) {
            //! message flash
            return $this->redirectToRoute('app_login');
        }

        $event = new Event;

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // GESTION DES IMAGES
            //on recupere les images transmise
            $pictures = $form->get('pictures')->getData();
            //on boucle sur les images
            foreach ($pictures as $picture) {
                //on genere un nouveau nom de fichier (codé) et on rajoute son extension
                $fichier = md5(uniqid()) . '.' . $picture->guessExtension();

                // on copie le fichier dans le dossier uploads
                // 2 params (destination, fichier)
                $picture->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                // on stock l'image dans la bdd (son nom)
                $img = new Picture();
                $img->setPath($fichier)
                    ->setTitle($event->getTitle())
                    ->setOrderPriority(1);
                $event->addPicture($img);
            }
            //FIN GESTION DES IMAGES

            /*Localisation de l'event*/
            $localisation = new Localisation();
            $localisation->setAdress($request->request->get('event')['localisation']['adress'])
                         ->setCityName($request->request->get('event')['localisation']['cityName'])
                         ->setCityCp($request->request->get('event')['localisation']['cityCp'])
                         ->setCoordonneesX($request->request->get('event')['localisation']['coordonneesX'])
                         ->setCoordonneesY($request->request->get('event')['localisation']['coordonneesY']);
            $em->persist($localisation);

            //creation de l'event (grace a localisation)
            $event->setSlug(strtolower($slugger->slug($event->getTitle())))
                ->setTag(strtoupper($slugger->slug($event->getTitle())))
                ->setCreatedAt(new DateTime())
                ->setUser($user)
                ->setLocalisation($localisation);
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
     * @Route("/event/edit/{event_id}", name="event_edit")
     */
    public function edit($event_id, SluggerInterface $slugger, EventRepository $eventRepository, Request $request, EntityManagerInterface $em)
    {

        $event = $eventRepository->find($event_id);

        if (!$event) {
            throw $this->createNotFoundException("l'event demandé n'existe pas!");
        }
        
        $this->denyAccessUnlessGranted('CAN_EDIT', $event, "Vous n'êtes pas le createur de cet évênement, vous ne pouvez pas l'éditer");
        

        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // GESTION DES IMAGES
            //on recupere les images transmise
            $pictures = $form->get('pictures')->getData();
            //on boucle sur les images
            foreach ($pictures as $picture) {
                //on genere un nouveau nom de fichier (codé) et on rajoute son extension
                $fichier = md5(uniqid()) . '.' . $picture->guessExtension();

                // on copie le fichier dans le dossier uploads
                // 2 params (destination, fichier)
                $picture->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                // on stock l'image dans la bdd (son nom)
                $img = new Picture();
                $img->setPath($fichier)
                    ->setTitle($event->getTitle())
                    ->setOrderPriority(1);
                $event->addPicture($img);
            }
            //FIN GESTION DES IMAGES

            /*Localisation de l'event*/
            $localisation = new Localisation();
            $localisation->setAdress($request->request->get('event')['localisation']['adress'])
                         ->setCityName($request->request->get('event')['localisation']['cityName'])
                         ->setCityCp($request->request->get('event')['localisation']['cityCp'])
                         ->setCoordonneesX($request->request->get('event')['localisation']['coordonneesX'])
                         ->setCoordonneesY($request->request->get('event')['localisation']['coordonneesY']);
            $em->persist($localisation);

            //creation de l'event (grace a localisation)
            $event->setSlug(strtolower($slugger->slug($event->getTitle())))
                ->setTag(strtoupper($slugger->slug($event->getTitle())))
                ->setLocalisation($localisation);
            $em->persist($event);

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

        $this->denyAccessUnlessGranted('CAN_DELETE', $event, "Vous n'êtes pas le createur de cet évênement, vous ne pouvez pas le supprimer");


        // traitement de la suppression
        $em->remove($event);
        $em->flush();
        // CA MARCHE MAIS J'AI PASSER EN CASCADE AU DELETE POUR TRANSPORT ET PICTURE
        // JE PENSE QUE C'EST LE COMPORTEMENT A FAIRE MAIS IL FAUT PREVENIR LES USERS QUE C'EST FAIT
        // MAIL A FAIRE JUSTE AVANT LE DELETE (MEME CHOSE POUR UPDATE)
        
        // redirection vers le dash organizer / events (avec message)
        return $this->redirectToRoute('event');
    }

    /**
     * @Route("/event/picture/delete/{id}", name="event_picture_delete", methods={"DELETE"})
     */
    public function deleteImage(Picture $picture, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        
        // on verifie si le token est valid
        if($this->isCsrfTokenValid('delete'.$picture->getId(), $data['_token'])) {
            // on recupere le nom de l'image
            $path = $picture->getPath();
            // on supprime le fichier
            unlink($this->getParameter('images_directory').'/'.$path);

            // on supprime l'entré de la base
            $em = $this->getDoctrine()->getManager();
            $em->remove($picture);
            $em->flush();

            // on repond en json
            return new JsonResponse(['success' => 1]);

        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }
}

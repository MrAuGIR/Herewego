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


/**
 * @Route("/event")
 */
class EventController extends AbstractController
{
    protected $em;
    protected $slugger;

    public function __construct(EntityManagerInterface $em, SluggerInterface $slugger)
    {
        $this->em = $em;
        $this->slugger = $slugger;
    }

    /**
     * @Route("/", name="event")
     */
    public function index(EventRepository $eventRepository)
    {
        $events = $eventRepository->findAll();

        return $this->render('event/index.html.twig', [
            'events' => $events
        ]);
    }

    /**
     * @Route("/show/{event_id}", name="event_show")
     */
    public function show($event_id, ParticipationRepository $participationRepository, EventRepository $eventRepository)
    {
        $event = $eventRepository->findOneBy([
            'id' => $event_id
        ]);
        if (!$event) {
            $this->addFlash('warning', "L'évênement demandé n'existe pas");
            return $this->redirectToRoute('event');
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

        // incrémente le nombre de vues
        $event->setCountViews($event->getCountViews()+1);
        $this->em->flush();

        return $this->render('event/show.html.twig', [
            'event' => $event,
            'user' => $user,
            'isOnEvent' => $isOnEvent,
            'countView' => $event->getCountViews()
        ]);
    }

    /**
     * @Route("/category/{category_id}", name="event_category")
     */
    public function category($category_id, CategoryRepository $categoryRepository)
    {
        $category = $categoryRepository->find($category_id);
        if (!$category) {
            $this->addFlash('warning', "La Catégorie demandée n'existe pas");
            return $this->redirectToRoute('event');
        }

        return $this->render('event/category.html.twig', [
            'category' => $category
        ]);
    }

    /**
     * @Route("/group/{group_id}", name="event_group")
     */
    public function group($group_id, EventGroupRepository $eventGroupRepository)
    {
        $eventGroup = $eventGroupRepository->find($group_id);
        if (!$eventGroup) {
            $this->addFlash('warning', "Le groupe demandé n'existe pas");
            return $this->redirectToRoute('event');
        }

        return $this->render('event/group.html.twig', [
            'eventGroup' => $eventGroup
        ]);
    }

    /**
     * @Route("/participate/{event_id}", name="event_participate")
     */
    public function participate($event_id, EventRepository $eventRepository, ParticipationRepository $participationRepository)
    {
        // recuperer l'event
        $event = $eventRepository->find($event_id);
        if (!$event) {
            $this->addFlash('warning', "L'évênement demandé n'existe pas");
            return $this->redirectToRoute('event');
        }

        // recuperer l'id du user connecté
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', "Connectez-vous pour participer à cet évênement");
            return $this->redirectToRoute('app_login');
        }

        // Verifie si le user participe déjà à cet event
        $participations = $participationRepository->findBy([
            'user' => $user->getId(),
            'event' => $event->getId()
        ]);
        if (!empty($participations)) {
            $this->addFlash('warning', "Vous participez déjà à cet évênement");
            return $this->redirectToRoute('event_show', [
                'event_id' => $event_id
            ]);
        }

        // rajouter une ligne dans la table participation        
        $participation = new Participation;
        $participation->setEvent($event)
            ->setUser($user)
            ->setAddedAt(new DateTime());        
        $this->em->persist($participation);
        $this->em->flush();

        // redirige avec message
        $this->addFlash('success', "Vous participez desormais à cet évênement");
        return $this->redirectToRoute('event_show', [
            'event_id' => $event_id
        ]);
    }

    /**
     * @Route("/cancel/{event_id}", name="event_cancel")
     */
    public function cancel($event_id, EventRepository $eventRepository, ParticipationRepository $participationRepository)
    {
        // recuperer l'event
        $event = $eventRepository->find($event_id);
        if (!$event) {
            $this->addFlash('warning', "L'évênement demandé n'existe pas");
            return $this->redirectToRoute('event');
        }

        // recuperer l'id du user connecté
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', "Connectez-vous pour annuler votre participation à cet évênement.");
            return $this->redirectToRoute('app_login');
        }

        // si le user participe pas on le redirige
        $participation = $participationRepository->findOneBy([
            'user' => $user->getId(),
            'event' => $event->getId()
        ]);
        if (empty($participation)) {
            $this->addFlash('warning', "Vous ne participez pas encore à cet évênement.");
            return $this->redirectToRoute('event_show', [
                'event_id' => $event_id
            ]);
        }

        // on supprime la participation     
        $this->em->remove($participation);
        $this->em->flush();

        // redirige vers la page de l'event (avec message success)
        $this->addFlash('success', "Vous avez annulé votre participation à cet évênement");
        return $this->redirectToRoute('event_show', [
            'event_id' => $event_id
        ]);
    }

    /**
     * @Route("/create", name="event_create")
     */
    public function create(Request $request)
    {
        
        // verifier si c'est un ORGANIZER
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', "Connectez-vous pour creer un évênement.");
            return $this->redirectToRoute('app_login');
        }

        $event = new Event;
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

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
                $this->em->persist($localisation);
    
                //creation de l'event (grace a localisation)
                $event->setSlug(strtolower($this->slugger->slug($event->getTitle())))
                    ->setTag(strtoupper($this->slugger->slug($event->getTitle())))
                    ->setCreatedAt(new DateTime())
                    ->setUser($user)
                    ->setLocalisation($localisation);
                $this->em->persist($event);
                $this->em->flush();
    
                $this->addFlash('success', "Vous avez créé un nouvel évênement");
                return $this->redirectToRoute('event_show', [
                    'event_id' => $event->getId()
                ]);
            } else {
                $this->addFlash('danger', "Veuillez remplir tous les champs obligatoires");
            }
        }

        $formView = $form->createView();

        return $this->render('event/create.html.twig', [
            'formView' => $formView
        ]);
    }

    /**
     * @Route("/edit/{event_id}", name="event_edit")
     */
    public function edit($event_id, SluggerInterface $slugger, EventRepository $eventRepository, Request $request)
    {

        $event = $eventRepository->find($event_id);

        if (!$event) {
            throw $this->createNotFoundException("l'event demandé n'existe pas!");
        }
        
        //! GERER les droits par un return et un message
        $this->denyAccessUnlessGranted('CAN_EDIT', $event, "Vous n'êtes pas le createur de cet évênement, vous ne pouvez pas l'éditer");
        

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

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
                $this->em->persist($localisation);
    
                //creation de l'event (grace a localisation)
                $event->setSlug(strtolower($slugger->slug($event->getTitle())))
                    ->setTag(strtoupper($slugger->slug($event->getTitle())))
                    ->setLocalisation($localisation);
                $this->em->persist($event);
    
                $this->em->flush();
                $this->addFlash('success', "Vous avez modifié votre évênement avec succés");
                return $this->redirectToRoute('event_show', [
                    'event_id' => $event->getId()
                ]);
            } else {
                $this->addFlash('danger', "Veuillez remplir tous les champs obligatoires");
            }
        }

        $formView = $form->createView();

        return $this->render('event/update.html.twig', [
            'formView' => $formView,
            'event' => $event

        ]);
    }

    /**
     * @Route("/delete/{event_id}", name="event_delete")
     */
    public function delete($event_id, EventRepository $eventRepository)
    {
        // recuperer l'event_id passé en param
        $event = $eventRepository->find($event_id);

        if (!$event) {
            throw $this->createNotFoundException("l'event demandé n'existe pas!");
        }

        //! GERER les droits par un return et un message
        $this->denyAccessUnlessGranted('CAN_DELETE', $event, "Vous n'êtes pas le createur de cet évênement, vous ne pouvez pas le supprimer");

        dd("traitement de la suppression de l'évent");

        // traitement de la suppression
        $this->em->remove($event);
        $this->em->flush();
        // CA MARCHE MAIS J'AI PASSER EN CASCADE AU DELETE POUR TRANSPORT ET PICTURE
        // JE PENSE QUE C'EST LE COMPORTEMENT A FAIRE MAIS IL FAUT PREVENIR LES USERS QUE C'EST FAIT
        // MAIL A FAIRE JUSTE AVANT LE DELETE (MEME CHOSE POUR UPDATE)
        
        // redirection vers le dash organizer / events (avec message)
        $this->addFlash('success', "La suppression de l'évênement a réussie");
        return $this->redirectToRoute('event');
    }

    /**
     * @Route("/picture/delete/{id}", name="event_picture_delete", methods={"DELETE"})
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
            $this->em = $this->getDoctrine()->getManager();
            $this->em->remove($picture);
            $this->em->flush();

            // on repond en json
            return new JsonResponse(['success' => 1]);

        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }
}

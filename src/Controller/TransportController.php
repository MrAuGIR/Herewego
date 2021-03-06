<?php

namespace App\Controller;


use DateTime;


use App\Entity\City;
use App\Entity\Event;
use App\Entity\Transport;
use App\Entity\Localisation;
use App\Entity\Ticket;
use App\Entity\User;
use App\Form\TransportType;
use App\Form\TicketType;
use App\Repository\CityRepository;
use App\Repository\EventRepository;
use App\Repository\TicketRepository;
use App\Repository\TransportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class TransportController extends AbstractController
{
    /**
     * Affiche les transport de l'event
     * @Route("/transport/event/{event_id}", name="transport")
     * 
     */
    public function index($event_id, EventRepository $eventRepository, Security $security)
    {
        $event = $eventRepository->find($event_id);

        /*Accès refusé si utilisateur non connecté*/
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var \App\Entity\User $user */
        $user = $security->getUser();

        /*On verifie que l'utilisateur participe a l'event lié à ces transport */
        $participationsUser = $user->getParticipations();
        $participating = false;
        foreach ($participationsUser as $participation) {
            /** @var \App\Entity\Event $eventUser */
            $eventUser = $participation->getEvent();
            if($eventUser->getId() == $event_id){
                $participating = true;
                break;
            }
        }
       
        //si l'utilisateur ne participe pas on redirige vers la page de l'event
        if(!$participating){
            return $this->redirectToRoute('event_show', [
                'event_id' => $event_id
            ]);
        }


        return $this->render('transport/index.html.twig', [
            'user' => $user,
            'event' => $event
        ]);
    }


    /**
     * @Route("/transport/show/{transport_id}", name="transport_show")
     */
    public function show($transport_id, TransportRepository $transportRepository,Security $security, Request $request, TicketRepository $ticketRepository, EntityManagerInterface $em):Response
    {
        //le user doit participer à l'event pour voir cette page
        /** @var \App\Entity\User $user */
        $user = $security->getUser();

        $transport = $transportRepository->find($transport_id);

        /* Verification si l'utilisateur a déjà un ticket sur ce transport*/
        $ticket = $ticketRepository->findOneByUserAndTransport($user,$transport);
        if(!$ticket){
            $ticket = new Ticket();
        }

        /*Creation du formulaire */
        $form = $this->createForm(TicketType::class, $ticket);

        $form->handleRequest($request);

        /* Soumission du formulaire demande de ticket */
        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->setAskedAt(new \DateTime('now'))
                ->setTransport($transport)
                ->setUser($user)
                ->setCountPlaces($request->request->get('ticket')['countPlaces'])
                ->setCommentary($request->request->get('ticket')['commentary']);
                

            $em->persist($ticket);
            $em->flush();

            // rediriger vers la page du transport (avec message success)
            return $this->redirectToRoute('transport_show', [
                'transport_id' => $transport_id
            ]);
        }


        return $this->render('transport/show.html.twig', [
            'user' => $user,
            'ticket' => $ticket,
            'transport' => $transport,
            'form'=>$form->createView(),
        ]);
    }

    /**
     * annulation du ticket par son émetteur
     * @Route("/transport/{transport_id}/cancelTicket/{id}", name="cancel_ticket")
     */
    public function cancelTicket(Ticket $ticket, Security $security, $transport_id){

        /** @var \App\Entity\User $user */
        $user = $security->getUser();

        /** @var Transport $transport */
        $transport = $ticket->getTransport();

        if (!$user) {
            // rediriger vers la page de connection
            $this->redirectToRoute('app_login');
        }
        /* on met a jour le nombre de place restante du transport*/
        $manager = $this->getDoctrine()->getManager();
        /* on met a jour le nombre de place uniquement si le ticket avait un status validé*/
        if($ticket->getIsValidate() == true){
            $transport->setRemainingPlace($transport->getRemainingPlace() + $ticket->getCountPlaces());
        }
        
        $manager->persist($transport);
        
        $manager->remove($ticket);
        $manager->flush();

        
         // rediriger vers la page du transport (avec message success)
        return $this->redirectToRoute('transport_show', [
            'transport_id' => $transport_id
        ]);
    }

    /**
     * @Route("/transport/manage/{transport_id}", name="transport_manage")
     */
    public function manage($transport_id, TransportRepository $transportRepository)
    {
        $transport = $transportRepository->find($transport_id);

        return $this->render('transport/manage.html.twig', [
            'transport' => $transport
        ]);
    }

    /**
     * @Route("/transport/manage/accept/{ticket_id}", name="transport_accept_ticket")
     */
    public function accept($ticket_id, TicketRepository $ticketRepository, EntityManagerInterface $em)
    {
        $ticket = $ticketRepository->find($ticket_id);

        /*on verifie le nombre de places restantes */
        /** @var Transport $transport */
        $transport = $ticket->getTransport();

        /* si le nombre de place est suffisant on met a jour (et si le ticket n'est pas déjà validé)*/
        if(($transport->getRemainingPlace()>= $ticket->getCountPlaces()) && ($ticket->getIsValidate()!= true)){

            $ticket->setIsValidate(true);
            $transport->setRemainingPlace($transport->getRemainingPlace() - $ticket->getCountPlaces());
            $ticket->setValidateAt(new DateTime());
            $em->persist($transport);
            $em->persist($ticket);
            $em->flush();

            // rediriger vers la page du transport (avec message success)
            return $this->redirectToRoute('transport_manage', [
                'transport_id' => $ticket->getTransport()->getId()
            ]);
        }

        // rediriger vers la page du transport (avec message success)
        return $this->redirectToRoute('transport_manage', [
            'transport_id' => $ticket->getTransport()->getId()
        ]);
        
        
    }

    /**
     * @Route("/transport/manage/decline/{ticket_id}", name="transport_decline_ticket")
     */
    public function decline($ticket_id, TicketRepository $ticketRepository, EntityManagerInterface $em)
    {
        $ticket = $ticketRepository->find($ticket_id);

        /* on recupère le transport lié au ticket */
        /** @var Transport $transport */
        $transport = $ticket->getTransport();

        /* si le ticket a precedement été validé alors dans ce cas la on remet a jour le nombre de place*/
        if($ticket->getIsValidate()!=false){
            $transport->setRemainingPlace($transport->getRemainingPlace() + $ticket->getCountPlaces());
            $em->persist($transport);
        }

        $ticket->setIsValidate(false);
        $em->persist($ticket);
        $em->flush();
        
        
        // dd($ticket);
        // rediriger vers la page du transport (avec message success)
        return $this->redirectToRoute('transport_manage', [
            'transport_id' => $ticket->getTransport()->getId()
        ]);
    }


    /**
     * @Route("/transport/create/{event_id}", name="transport_create")
     */
    public function create(Request $request, $event_id,EventRepository $eventRepository, Security $security, EntityManagerInterface $em)
    {
        /* CONDITION pour créer un nouveau transport
        * - Etre connecté
        * - Participer a l'evenement
        * - Ne pas déjà avoir créé un transport pour cet évent
        */
        
        //Recuperation de l'event concerné par le transport
        $event = $eventRepository->find($event_id);

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        //Recuperation de l'utilisateur connecté
        /** @var \App\Entity\User $user */
        $user = $security->getUser();

        if (!$user) {
            // rediriger vers la page de connection
            $this->redirectToRoute('app_login');
        }

        $allow = $this->allowCreateTransport($user,$event);
        
        /* redirection si l'utilisateur n'est pas autorisé à créer */
        if (!$allow) {
            return $this->redirectToRoute('event_show', ['event_id' => $event_id]);
        }

        //Instanciation d'un nouvel objet transport
        $transport = new Transport();
        //Creation de l'objet formulaire
        $form = $this->createForm(TransportType::class,$transport);

        $form->handleRequest($request);

        //Soumission du formulaire
        if ($form->isSubmitted() && $form->isValid()){
            
            /*Localisation de départ (aller) */
            $localisationStart = new Localisation();
            
            //$cityStart = $this->getDoctrine()->getRepository(City::class)->find($idCityStart);
            $localisationStart->setAdress($request->request->get('transport')['localisation_start']['adress'])
                              ->setCityCp($request->request->get('transport')['localisation_start']['cityCp'])
                              ->setCityName($request->request->get('transport')['localisation_start']['cityName'])
                              ->setCoordonneesX($request->request->get('transport')['localisation_start']['coordonneesX'])
                              ->setCoordonneesY($request->request->get('transport')['localisation_start']['coordonneesY']);
            $em->persist($localisationStart);

            /*Date et heure de départ (aller) */
            $gostartedAt = $request->request->get('transport')['goStartedAt'];

            /*Date et heure d'arrivé (aller) */
            $goEndedAt = $request->request->get('transport')['goEndedAt'];

            /*Localisation de retour (au retour) */
            $localisationReturn = new Localisation();

            $localisationReturn->setAdress($request->request->get('transport')['localisation_return']['adress'])
                              ->setCityCp($request->request->get('transport')['localisation_return']['cityCp'])
                              ->setCityName($request->request->get('transport')['localisation_return']['cityName'])
                              ->setCoordonneesX($request->request->get('transport')['localisation_return']['coordonneesX'])
                              ->setCoordonneesY($request->request->get('transport')['localisation_return']['coordonneesY']);
            $em->persist($localisationReturn);

            /*Date et heure de départ (au retour) */
            $returnStartedAt = $request->request->get('transport')['returnStartedAt'];

            /*Date et heure d'arrivé (au retour ) */
            $returnEndedAt = $request->request->get('transport')['returnEndedAt'];

            /*Prix des places */
            $placePrice = $request->request->get('transport')['placePrice'];

            /*Nombre de places */
            $totalPlace = $request->request->get('transport')['totalPlace'];

            /*Commentaire du createur */
            $commentary = $request->request->get('transport')['commentary'];

            /*Creation du transport*/
            $transport->setUser($user)
                      ->setEvent($event)
                      ->setLocalisationStart($localisationStart)
                      ->setLocalisationReturn($localisationReturn)
                      ->setGoStartedAt(new \DateTime($gostartedAt['date'].' '.$gostartedAt['time']))
                      ->setGoEndedAt(new \DateTime($goEndedAt['date'].' '.$goEndedAt['time']))
                      ->setReturnStartedAt(new \DateTime($returnStartedAt['date'].' '.$returnStartedAt['time']))
                      ->setReturnEndedAt(new \DateTime($returnEndedAt['date'].' '.$returnEndedAt['time']))
                      ->setPlacePrice($placePrice)
                      ->setTotalPlace($totalPlace)
                      ->setCommentary($commentary)
                      ->setRemainingPlace($totalPlace)
                      ->setCreatedAt(new \DateTime())
            ;
            $em->persist($transport);
            $em->flush();

            return $this->redirectToRoute('transport',['event_id'=>$event_id]);

        }


        return $this->render('transport/create.html.twig', [
            'eventId' => $event_id,
            'event' => $event,
            'form'=>$form->createView(),
        ]);
    }

    /**
     * @Route("/transport/edit/{id}", name="transport_edit", methods={"GET","POST"})
     */
    public function edit(Transport $transport, Request $request, EntityManagerInterface $em, Security $security):Response
    {
        /*Création du transport*/
        // $transport = $transportRepository->find($transport_id);

        /*Si transport Inexistant */
        if (!$transport) {
            throw $this->createNotFoundException("le transport demandé n'existe pas!");
        }

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        //Recuperation de l'utilisateur connecté
        /** @var \App\Entity\User $user */
        $user = $security->getUser();

        /*Si pas user -> on redirige*/
        if (!$user) {
            // rediriger vers la page de connection
            $this->redirectToRoute('home');
        }

        $form= $this->createForm(TransportType::class, $transport);
        $form->handleRequest($request);

        //Soumission du formulaire
        if ($form->isSubmitted() && $form->isValid()) {

            /*Localisation de départ (aller) */
            $localisationStart = $transport->getLocalisationStart();

            //$cityStart = $this->getDoctrine()->getRepository(City::class)->find($idCityStart);
            $localisationStart->setAdress($request->request->get('transport')['localisation_start']['adress'])
                ->setCityCp($request->request->get('transport')['localisation_start']['cityCp'])
                ->setCityName($request->request->get('transport')['localisation_start']['cityName'])
                ->setCoordonneesX($request->request->get('transport')['localisation_start']['coordonneesX'])
                ->setCoordonneesY($request->request->get('transport')['localisation_start']['coordonneesY']);
            $em->persist($localisationStart);

            /*Date et heure de départ (aller) */
            $gostartedAt = $request->request->get('transport')['goStartedAt'];

            /*Date et heure d'arrivé (aller) */
            $goEndedAt = $request->request->get('transport')['goEndedAt'];

            /*Localisation de retour (au retour) */
            $localisationReturn = $transport->getLocalisationReturn();

            $localisationReturn->setAdress($request->request->get('transport')['localisation_return']['adress'])
                ->setCityCp($request->request->get('transport')['localisation_return']['cityCp'])
                ->setCityName($request->request->get('transport')['localisation_return']['cityName'])
                ->setCoordonneesX($request->request->get('transport')['localisation_return']['coordonneesX'])
                ->setCoordonneesY($request->request->get('transport')['localisation_return']['coordonneesY']);
            $em->persist($localisationReturn);

            /*Date et heure de départ (au retour) */
            $returnStartedAt = $request->request->get('transport')['returnStartedAt'];

            /*Date et heure d'arrivé (au retour ) */
            $returnEndedAt = $request->request->get('transport')['returnEndedAt'];

            /*Prix des places */
            $placePrice = $request->request->get('transport')['placePrice'];

            /*Nombre de places */
            $totalPlace = $request->request->get('transport')['totalPlace'];

            /*Commentaire du createur */
            $commentary = $request->request->get('transport')['commentary'];

            /*Creation du transport*/
            $transport->setUser($user)
                ->setLocalisationStart($localisationStart)
                ->setLocalisationReturn($localisationReturn)
                ->setGoStartedAt(new \DateTime($gostartedAt['date'] . ' ' . $gostartedAt['time']))
                ->setGoEndedAt(new \DateTime($goEndedAt['date'] . ' ' . $goEndedAt['time']))
                ->setReturnStartedAt(new \DateTime($returnStartedAt['date'] . ' ' . $returnStartedAt['time']))
                ->setReturnEndedAt(new \DateTime($returnEndedAt['date'] . ' ' . $returnEndedAt['time']))
                ->setPlacePrice($placePrice)
                ->setTotalPlace($totalPlace)
                ->setCommentary($commentary)
                ->setRemainingPlace($totalPlace);
            $em->persist($transport);
            $em->flush();

            return $this->redirectToRoute('transport', ['event_id' => $transport->getEvent()->getId()]);
        }



        return $this->render('transport/edit.html.twig', [
            'transport'=>$transport,
            'event'=>$transport->getEvent(),
            'user'=>$user,
            'form'=>$form->createView(),

        ]);
    }

    /**
     * @Route("/transport/delete/{transport_id}", name="transport_delete")
     */
    public function delete($transport_id)
    {

        // gestion de la suppression d'un transport

        // redirection 

        dd($transport_id);

        // return $this->render('transport/delete.html.twig');
    }

    /**
     * allowCreateTransport
     * Autorise ou non l'utilisateur a créé un transport
     * @param  mixed $user
     * @param  mixed $event
     * @return bool
     */
    public function allowCreateTransport(User $user, Event $event):bool{
        $participating = $this->isParticipating($user,$event);
        /*L'utilisateur participe a l'event */
        if($participating){
           if(!$this->alreadyManageTransport($user, $event)){
               return True;
           }
        }
        return False;

    }

    /**
     * isParticipating
     * Verifie que l'utilisateur participe a l'event
     * @param  mixed $user
     * @param  mixed $event
     * @return bool renvoie true si est bien inscrit a l'event false sinon
     */
    public function isParticipating(User $user, Event $event):bool{
        $participations = $user->getParticipations();

        foreach($participations as $participation){
            if($participation->getEvent()->getId() == $event->getId()){
                return True;
            }
        }
        return False;
    }
    
    /**
     * alreadyManageTransport
     * Verifiy si l'utilisateur n'a pas déjà un transport (en tant que manager)
     * sur cet event
     * @param  mixed $user
     * @param  mixed $event
     * @return bool  renvoie true si deja manager, false sinon
     */
    public function alreadyManageTransport(User $user, Event $event):bool{
        $transports = $event->getTransports();

        foreach($transports as $transport){
            if($transport->getUser()->getId() == $user->getId() ){
                return True;
            }
        }
        return False;
    }
}

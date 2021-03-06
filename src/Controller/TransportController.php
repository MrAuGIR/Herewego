<?php

namespace App\Controller;


use DateTime;


use App\Entity\City;
use App\Entity\Transport;
use App\Entity\Localisation;
use App\Entity\Ticket;
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
     * @Route("/transport/event/{event_id}", name="transport")
     */
    public function index($event_id, EventRepository $eventRepository, Security $security)
    {
        $event = $eventRepository->find($event_id);

        /** @var \App\Entity\User $user */
        $user = $security->getUser();


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
        $alreadyAsk = true;

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
                ->setCommentary($request->request->get('ticket')['commentary'])
                ->setIsValidate(false);

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
     * @Route("/transport/{transport_id}/cancelTicket/{id}", name="cancel_ticket")
     */
    public function cancelTicket(Ticket $ticket, Security $security, $transport_id){

        /** @var \App\Entity\User $user */
        $user = $security->getUser();


        if (!$user) {
            // rediriger vers la page de connection
            $this->redirectToRoute('app_login');
        }

        $manager = $this->getDoctrine()->getManager();
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

        $ticket->setIsValidate(true);
        $ticket->setValidateAt(new DateTime());
        $em->flush();
        
        // dd($ticket);
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

        $ticket->setIsValidate(false);
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
     * @Route("/transport/{id}/ticket", name="ask_ticket")
     */
}

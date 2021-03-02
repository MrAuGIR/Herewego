<?php

namespace App\Controller;


use DateTime;

use App\Form\TransportType;
use App\Entity\City;
use App\Entity\Transport;
use App\Entity\Localisation;
use App\Repository\CityRepository;
use App\Repository\EventRepository;
use App\Repository\TicketRepository;
use App\Repository\TransportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class TransportController extends AbstractController
{
    /**
     * @Route("/transport/event/{event_id}", name="transport")
     */
    public function index($event_id, EventRepository $eventRepository)
    {
        $event = $eventRepository->find($event_id);


        return $this->render('transport/index.html.twig', [
            'event' => $event
        ]);
    }


    /**
     * @Route("/transport/show/{transport_id}", name="transport_show")
     */
    public function show($transport_id, TransportRepository $transportRepository)
    {
        //le user doit participer à l'event pour voir cette page

        $transport = $transportRepository->find($transport_id);

        return $this->render('transport/show.html.twig', [
            'transport' => $transport
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

        //Recuperation de l'utilisateur connecté
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
            
            dump($request);
            /*Localisation de départ (aller) */
            $localisationStart = new Localisation();
            $cityStart = new City();
            $idCityStart = $request->request->get('transport')['localisation_start']['city'];
            $cityStart = $this->getDoctrine()->getRepository(City::class)->find($idCityStart);
            
            $localisationStart->setCity($cityStart)
                              ->setAdress($request->request->get('transport')['localisation_start']['adress']);
            $em->persist($localisationStart);

            /*Date et heure de départ (aller) */
            $gostartedAt = $request->request->get('transport')['goStartedAt'];

            /*Date et heure d'arrivé (aller) */
            $goEndedAt = $request->request->get('transport')['goEndedAt'];

            /* localisation de retour (au retour) */
            $localisationReturn = new Localisation();
            $cityReturn= new City();
            $idCityReturn = $request->request->get('transport')['localisation_return']['city'];
            $cityReturn = $this->getDoctrine()->getRepository(City::class)->find($idCityReturn);

            $localisationReturn->setCity($cityReturn)
                              ->setAdress($request->request->get('transport')['localisation_return']['adress']);
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

            dump($transport);

        }


        return $this->render('transport/create.html.twig', [
            'eventId' => $event_id,
            'event' => $event,
            'form'=>$form->createView(),
        ]);
    }

    /**
     * @Route("/transport/edit/{transport_id}", name="transport_edit")
     */
    public function edit($transport_id)
    {
        return $this->render('transport/edit.html.twig', [
            'transportId' => $transport_id,
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
}

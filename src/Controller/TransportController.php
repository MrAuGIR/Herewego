<?php

namespace App\Controller;

use DateTime;
use App\Repository\EventRepository;
use App\Repository\TicketRepository;
use App\Repository\TransportRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    public function create($event_id)
    {
        return $this->render('transport/create.html.twig', [
            'eventId' => $event_id,
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

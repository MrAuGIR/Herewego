<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Ticket;
use App\Entity\Transport;
use App\Entity\User;
use App\Form\TicketType;
use App\Form\TransportType;
use App\Repository\TicketRepository;
use App\Security\Voter\TransportVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/transport', name: 'transport')]
class TransportController extends AbstractController
{

    #[Route("/event/{id}", name: '', methods: [Request::METHOD_GET])]
    public function index(Event $event): RedirectResponse|Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->getUser();

        $participationsUser = $user->getParticipations();
        $participating = false;
        foreach ($participationsUser as $participation) {
            /** @var Event $eventUser */
            $eventUser = $participation->getEvent();
            if ($eventUser->getId() == $event->getId()) {
                $participating = true;
                break;
            }
        }

        if (! $participating) {
            $this->addFlash('warning', 'Vous devez participer a l\'event pour voir ses transports');

            return $this->redirectToRoute('event_show', [
                'id' => $event->getId(),
            ]);
        }


        return $this->render('transport/index.html.twig', [
            'user' => $user,
            'event' => $event,
        ]);
    }

    #[Route('/show/{id}', name: '_show', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function show(Transport $transport, Request $request, TicketRepository $ticketRepository, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $ticket = $ticketRepository->findOneByUserAndTransport($user, $transport);
        if (!$ticket) {
            $ticket = new Ticket();
        }

        /* Creation du formulaire */
        $form = $this->createForm(TicketType::class, $ticket);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $ticket->setAskedAt(new \DateTime('now'))
                ->setTransport($transport)
                ->setUser($user)
                ;

            $em->persist($ticket);
            $em->flush();

            // rediriger vers la page du transport (avec message success)
            $this->addFlash('success', 'Demande de transport effectué');

            return $this->redirectToRoute('transport_show', [
                'id' => $transport->getId(),
            ]);
        }

        return $this->render('transport/show.html.twig', [
            'user' => $user,
            'ticket' => $ticket,
            'transport' => $transport,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{transport_id}/cancelTicket/{id}", name: "_cancel_ticket")]
    public function cancelTicket(int $transport_id,Ticket $ticket, EntityManagerInterface $em): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var Transport $transport */
        $transport = $ticket->getTransport();

        if (!$user) {
            $this->redirectToRoute('app_login');
        }

        if (! $this->isGranted('delete', $ticket)) {
            $this->addFlash('danger', 'action non autorisé');
            return $this->redirectToRoute('transport_show', ['transport_id' => $transport_id]);
        }

        /* on met a jour le nombre de place uniquement si le ticket avait un status validé */
        if ($ticket->getIsValidate()) {
            $transport->setRemainingPlace($transport->getRemainingPlace() + $ticket->getCountPlaces());
        }

        $em->persist($transport);
        $em->remove($ticket);
        $em->flush();

        $this->addFlash('info', 'votre ticket est annulé');

        return $this->redirectToRoute('transport_show', [
            'id' => $transport_id,
        ]);
    }


    #[Route('/manage/{id}', name: '_manage', methods: [Request::METHOD_GET])]
    public function manage(Transport $transport): RedirectResponse|Response
    {
        if (! $this->isGranted('manage', $transport)) {
            $this->addFlash('danger', 'Vous ne pouvez pas gérer ce transport');

            return $this->redirectToRoute('transport_show', ['id' => $transport->getId()]);
        }

        return $this->render('transport/manage.html.twig', [
            'transport' => $transport,
        ]);
    }

    #[Route("/manage/accept/{id}", name: "_accept_ticket", methods: [Request::METHOD_GET])]
    public function accept(Ticket $ticket, EntityManagerInterface $em): RedirectResponse
    {
        /** @var Transport $transport */
        $transport = $ticket->getTransport();

        if (($transport->getRemainingPlace() >= $ticket->getCountPlaces()) && !$ticket->getIsValidate()) {
            $ticket->setIsValidate(true);
            $transport->setRemainingPlace($transport->getRemainingPlace() - $ticket->getCountPlaces());
            $ticket->setValidateAt(new \DateTime());
            $em->persist($transport);
            $em->persist($ticket);
            $em->flush();

            // rediriger vers la page du transport (avec message success)
            $this->addFlash('success', 'ticket validé');

            return $this->redirectToRoute('transport_manage', [
                'id' => $ticket->getTransport()->getId(),
            ]);
        }

        return $this->redirectToRoute('transport_manage', [
            'id' => $ticket->getTransport()->getId(),
        ]);
    }

    #[Route("/manage/decline/{id}", name: "_decline_ticket")]
    public function decline(Ticket $ticket, EntityManagerInterface $em): RedirectResponse
    {
        /** @var Transport $transport */
        $transport = $ticket->getTransport();

        if (! $this->isGranted('manage', $transport)) {
            return $this->redirectToRoute('transport_show', ['transport_id' => $transport->getId()]);
        }

        if ($ticket->getIsValidate()) {
            $transport->setRemainingPlace($transport->getRemainingPlace() + $ticket->getCountPlaces());
            $em->persist($transport);
        }

        $ticket->setIsValidate(false);
        $em->persist($ticket);
        $em->flush();

        $this->addFlash('success', 'ticket annulé');

        return $this->redirectToRoute('transport_manage', [
            'id' => $ticket->getTransport()->getId(),
        ]);
    }

    #[Route("/create/{id}", name: "_create", methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Event $event,Request $request, EntityManagerInterface $em): RedirectResponse|Response
    {
        /* CONDITION pour créer un nouveau transport
        * - Etre connecté
        * - Participer a l'evenement
        * - Ne pas déjà avoir créé un transport pour cet évent
        */

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            $this->redirectToRoute('app_login');
        }

        $allow = $this->allowCreateTransport($user, $event);

        if (!$allow) {
            $this->addFlash('warning', 'Pour créer un transport vous devez être participant et ne pas avoir déjà créé de  précédent transport sur cet event');

            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }

        $transport = new Transport();

        $form = $this->createForm(TransportType::class, $transport);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /* Creation du transport */
            $transport->setUser($user)
                      ->setEvent($event)
                      ->setRemainingPlace($transport->getTotalPlace())
                      ->setCreatedAt(new \DateTime())
            ;
            $em->persist($transport);
            $em->flush();

            $this->addFlash('success', 'Transport créé');

            return $this->redirectToRoute('transport', ['id' => $event->getId()]);
        }

        return $this->render('transport/create.html.twig', [
            'eventId' => $event->getId(),
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/edit/{id}", name: "_edit", methods: [Request::METHOD_POST, Request::METHOD_GET])]
    #[IsGranted(TransportVoter::EDIT, 'transport')]
    public function edit(Transport $transport, Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            $this->redirectToRoute('home');
        }

        $form = $this->createForm(TransportType::class, $transport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($transport);
            $em->flush();

            $this->addFlash('success', 'Transport modifié');

            return $this->redirectToRoute('transport', ['id' => $transport->getEvent()->getId()]);
        }

        return $this->render('transport/edit.html.twig', [
            'transport' => $transport,
            'event' => $transport->getEvent(),
            'user' => $user,
            'form' => $form->createView(),

        ]);
    }

    #[Route("/delete/{id}", name: "_delete", methods: [Request::METHOD_DELETE, Request::METHOD_GET])]
    #[IsGranted(TransportVoter::DELETE, 'transport')]
    public function delete(Transport $transport, EntityManagerInterface $em): Response
    {
        $event_id = $transport->getEvent()->getId();

        $em->remove($transport);
        $em->flush();

        $this->addFlash('success', 'transport supprimé');

        return $this->redirectToRoute('event_show', ['id' => $event_id]);
    }

    /**
     * allowCreateTransport
     * Autorise ou non l'utilisateur a créé un transport.
     *
     * @param mixed $user
     * @param mixed $event
     */
    public function allowCreateTransport(User $user, Event $event): bool
    {
        if ($this->isParticipating($user, $event)) {
            if (! $this->alreadyManageTransport($user, $event)) {
                return true;
            }
        }

        return false;
    }

    /**
     * isParticipating
     * Verifie que l'utilisateur participe a l'event.
     *
     * @param mixed $user
     * @param mixed $event
     *
     * @return bool renvoie true si est bien inscrit a l'event false sinon
     */
    public function isParticipating(User $user, Event $event): bool
    {
        foreach ($user->getParticipations() as $participation) {
            if ($participation->getEvent()->getId() == $event->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * alreadyManageTransport
     * Verifiy si l'utilisateur n'a pas déjà un transport (en tant que manager)
     * sur cet event.
     *
     * @param mixed $user
     * @param mixed $event
     *
     * @return bool renvoie true si deja manager, false sinon
     */
    public function alreadyManageTransport(User $user, Event $event): bool
    {
        foreach ($event->getTransports() as $transport) {
            if ($transport->getUser()->getId() == $user->getId()) {
                return true;
            }
        }
        return false;
    }
}

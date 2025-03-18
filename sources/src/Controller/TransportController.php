<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Ticket;
use App\Entity\Transport;
use App\Entity\User;
use App\Factory\TickerFactory;
use App\Form\TicketType;
use App\Form\TransportType;
use App\Repository\TicketRepository;
use App\Security\Voter\EventVoter;
use App\Security\Voter\TicketVoter;
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
    #[IsGranted(EventVoter::VIEW, 'event')]
    public function index(Event $event): RedirectResponse|Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('transport/index.html.twig', [
            'user' => $user,
            'event' => $event,
        ]);
    }

    #[Route('/show/{id}', name: '_show', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function show(Transport $transport, Request $request, TicketRepository $ticketRepository, TickerFactory $factory): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (empty($ticket = $ticketRepository->findOneByUserAndTransport($user, $transport))) {
            $ticket = new Ticket();
        }

        $form = $this->createForm(TicketType::class, $ticket);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $factory->createFromRequest($transport, $ticket,$user);

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
    #[IsGranted(TicketVoter::DELETE, 'ticket')]
    public function cancelTicket(int $transport_id,Ticket $ticket, EntityManagerInterface $em): RedirectResponse
    {
        $transport = $ticket->getTransport();

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
    #[IsGranted(TransportVoter::MANAGE, 'transport')]
    public function manage(Transport $transport): RedirectResponse|Response
    {
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
    #[IsGranted(TransportVoter::MANAGE, 'transport')]
    public function decline(Ticket $ticket, EntityManagerInterface $em): RedirectResponse
    {
        /** @var Transport $transport */
        $transport = $ticket->getTransport();

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
    #[IsGranted(EventVoter::CREATE_TRANSPORT, 'event')]
    public function create(Event $event,Request $request, EntityManagerInterface $em): RedirectResponse|Response
    {
        /** @var User $user */
        $user = $this->getUser();

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
            'user' => $this->getUser(),
            'form' => $form->createView(),

        ]);
    }

    #[Route("/delete/{id}", name: "_delete", methods: [Request::METHOD_DELETE, Request::METHOD_GET])]
    #[IsGranted(TransportVoter::DELETE, 'transport')]
    public function delete(Transport $transport, EntityManagerInterface $em): Response
    {
        $em->remove($transport);
        $em->flush();

        $this->addFlash('success', 'transport supprimé');

        return $this->redirectToRoute('event_show', ['id' => $transport->getEvent()->getId()]);
    }
}

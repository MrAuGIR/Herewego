<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\User;
use App\Factory\EventFactory;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\ParticipationRepository;
use App\Service\Mail\Sender;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/event')]
#[IsGranted('ROLE_ADMIN', message: '404 page not found', statusCode: 404)]
class EventCrudController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
        private readonly EventFactory $eventFactory,
    ) {
    }

    #[Route('/', name: 'eventcrud', methods: [Request::METHOD_GET])]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('admin/event/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }

    #[Route('/create', name: 'eventcrud_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->eventFactory->create($form, $event, $user);

                $this->addFlash('success', 'Vous avez créé un nouvel évênement');

                return $this->redirectToRoute('eventcrud');
            }
        } else {
            $this->addFlash('danger', 'Veuillez remplir tous les champs obligatoires');
        }

        return $this->render('admin/event/create.html.twig', [
            'formView' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'eventcrud_edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(Event $event, Request $request): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->eventFactory->edit($form, $event);
                $this->addFlash('success', 'Vous avez modifié votre évênement avec succés');

                return $this->redirectToRoute('eventcrud');
            } else {
                $this->addFlash('danger', 'Veuillez remplir tous les champs obligatoires');
            }
        }

        return $this->render('admin/event/edit.html.twig', [
            'formView' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/show/{id}', name: 'eventcrud_show', methods: [Request::METHOD_GET])]
    public function show(Event $event, ParticipationRepository $participationRepository): Response
    {
        $user = $this->getUser();

        $isOnEvent = false;
        if ($user) {
            $participations = $participationRepository->findBy([
                'user' => $user->getId(),
                'event' => $event->getId(),
            ]);
            if (! empty($participations)) {
                $isOnEvent = true;
            }
        }

        $event->setCountViews($event->getCountViews() + 1);
        $this->em->flush();

        return $this->render('admin/event/show.html.twig', [
            'event' => $event,
            'user' => $user,
            'isOnEvent' => true,
            'countView' => $event->getCountViews(),
        ]);
    }

    #[Route('/delete/{id}', name: 'eventcrud_delete', methods: [Request::METHOD_DELETE])]
    public function delete(Event $event, Sender $sender): RedirectResponse
    {
        $sender->send($event, Sender::EVENT_DELETE, $this->getUser());

        $this->em->remove($event);
        $this->em->flush();

        $this->addFlash('success', 'evenement supprimé');

        return $this->redirectToRoute('eventcrud');
    }
}

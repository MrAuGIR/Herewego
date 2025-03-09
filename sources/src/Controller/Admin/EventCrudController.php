<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Factory\PictureFactory;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\ParticipationRepository;
use App\Tools\TagService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/event')]
#[IsGranted('ROLE_ADMIN', message: '404 page not found', statusCode: 404)]
class EventCrudController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected SluggerInterface $slugger,
        protected TagService $tag,
        private readonly PictureFactory $pictureFactory,
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
        $user = $this->getUser();

        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                foreach ($this->pictureFactory->handleFromForm($form) as $picture) {
                    $picture->setTitle($event->getTitle());
                    $event->addPicture($picture);
                }

                $event->setSlug(strtolower($this->slugger->slug($event->getTitle())))
                        ->setTag('pro')
                        ->setCreatedAt(new \DateTime())
                        ->setUser($user)
                ;

                $this->em->persist($event);
                $this->em->flush();

                $tagCode = $this->tag->makeTagCode($event);

                $event->setTag($this->tag->createHtmlTag($tagCode, $event->getId(), $event->getTitle()));
                $this->em->flush();

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
                foreach ($this->pictureFactory->handleFromForm($form) as $picture) {
                    $picture->setTitle($event->getTitle());
                    $event->addPicture($picture);
                }

                $event->setSlug(strtolower($this->slugger->slug($event->getTitle())));
                $tagCode = $this->tag->makeTagCode($event);
                $event->setTag($this->tag->createHtmlTag($tagCode, $event->getId(), $event->getTitle()));

                $this->em->persist($event);
                $this->em->flush();
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
    public function show(Event $event, ParticipationRepository $participationRepository)
    {
        if (! $event) {
            $this->addFlash('warning', "L'évênement demandé n'existe pas");

            return $this->redirectToRoute('eventcrud');
        }

        // recuperer l'id du user connecté // si pas connecté $user = Null
        $user = $this->getUser();

        // si user connecté, on regarde si il participe à l'event en cours
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

        // incrémente le nombre de vues
        $event->setCountViews($event->getCountViews() + 1);
        $this->em->flush();

        return $this->render('admin/event/show.html.twig', [
            'event' => $event,
            'user' => $user,
            'isOnEvent' => true, // c'est l'admin il a tous les pouvoirs
            'countView' => $event->getCountViews(),
        ]);
    }

    #[Route('/delete/{id}', name: 'eventcrud_delete', methods: [Request::METHOD_DELETE])]
    public function delete(Event $event, MailerInterface $mailer)
    {
        if (! $event) {
            throw $this->createNotFoundException("l'event demandé n'existe pas!");
        }

        $transports = $event->getTransports();
        $transportManagerMails = [];

        $ticketUserMails = [];

        foreach ($transports as $transport) {
            $transportManagerMails[] = $transport->getUser()->getEmail();

            $tickets = $transport->getTickets();
            foreach ($tickets as $ticket) {
                $ticketUserMails[] = $ticket->getUser()->getEmail();
            }
        }

        $email = new TemplatedEmail();
        $email->from(new Address('admin@gmail.com', 'Admin'))
        ->subject("Annulation de l'évênement : ".$event->getTitle())
            ->to(...$transportManagerMails, ...$ticketUserMails)
            ->htmlTemplate('emails/annulation_event.html.twig')
            ->context([
                'event' => $event,
            ]);
        $mailer->send($email);


        // traitement de la suppression
        $this->em->remove($event);
        $this->em->flush();

        $this->addFlash('success', 'evenement supprimé');

        return $this->redirectToRoute('eventcrud');
    }
}

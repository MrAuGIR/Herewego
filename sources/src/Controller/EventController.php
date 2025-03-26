<?php

namespace App\Controller;

use App\Dto\EventQueryDto;
use App\Entity\Category;
use App\Entity\Event;
use App\Entity\EventGroup;
use App\Entity\Picture;
use App\Entity\User;
use App\Factory\EventFactory;
use App\Factory\ParticipationFactory;
use App\Form\EventType;
use App\Repository\CategoryRepository;
use App\Repository\EventRepository;
use App\Repository\PictureRepository;
use App\Security\Voter\EventVoter;
use App\Service\Files\PictureService;
use App\Service\Mail\Sender;
use App\Tools\TagService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/event')]
class EventController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected SluggerInterface $slugger,
        protected TagService $tag,
        protected MailerInterface $mailer,
        private readonly EventFactory $eventFactory,
        private readonly ParticipationFactory $participationFactory,
        private readonly Sender $sender,
    ) {
    }

    #[Route('/', name: 'event', methods: [Request::METHOD_POST, Request::METHOD_GET])]
    public function index(#[MapQueryString] EventQueryDto $dto, EventRepository $eventRepository, CategoryRepository $categoryRepository, Request $request): JsonResponse|Response
    {
        $events = $eventRepository->findByFilters($dto);

        $total = $eventRepository->getCountEvent($dto);

        if ($request->get('ajax')) {
            return new JsonResponse([
                'content' => $this->renderView('event/_content.html.twig', [
                    'events' => $events,
                    'total' => $total,
                    'limit' => $dto->limit,
                    'page' => $dto->page,
                    'order' => $dto->order,
                ]),
            ]);
        }

        $categories = $categoryRepository->findAll();

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'categories' => $categories,
            'total' => $total,
            'limit' => $dto->limit,
            'page' => $dto->page,
        ]);
    }

    #[Route('/show/{id}', name: 'event_show', methods: [Request::METHOD_GET])]
    public function show(Event $event, PictureRepository $pictureRepository): RedirectResponse|Response
    {
        $pictures = $pictureRepository->findBy(['event' => $event->getId()], ['orderPriority' => 'DESC']);

        $user = $this->getCurrentUser();

        $isOnEvent = false;
        if ($user) {
            if (! empty($this->participationFactory->getUserParticipation($event, $user))) {
                $isOnEvent = true;
            }
        }

        $event->setCountViews($event->getCountViews() + 1);
        $this->em->flush();

        return $this->render('event/show.html.twig', [
            'event' => $event,
            'user' => $user,
            'pictures' => $pictures,
            'isOnEvent' => $isOnEvent,
            'countView' => $event->getCountViews(),
        ]);
    }

    #[Route('/category/{id}', name: 'event_category', methods: [Request::METHOD_GET])]
    public function category(Category $category): RedirectResponse|Response
    {
        return $this->render('event/category.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/group/{id}', name: 'event_group', methods: [Request::METHOD_GET])]
    public function group(EventGroup $eventGroup): RedirectResponse|Response
    {
        return $this->render('event/group.html.twig', [
            'eventGroup' => $eventGroup,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/participate/{id}', name: 'event_participate', methods: [Request::METHOD_GET])]
    #[IsGranted(EventVoter::CAN_PARTICIPATE, 'event')]
    public function participate(Event $event): RedirectResponse
    {
        $user = $this->getCurrentUser();

        $this->participationFactory->addParticipation($event, $user);

        $this->sender->sendEventParticipation($event, $user);

        $this->addFlash('success', 'Vous participez desormais à cet évênement');

        return $this->redirectToRoute('event_show', [
            'id' => $event->getId(),
        ]);
    }

    #[Route('/cancel/{id}', name: 'event_cancel', methods: [Request::METHOD_GET])]
    #[IsGranted(EventVoter::CAN_CANCEL, 'event')]
    public function cancel(Event $event): RedirectResponse
    {
        $this->participationFactory->cancelParticipation($event, $this->getCurrentUser());

        $this->addFlash('success', 'Vous avez annulé votre participation à cet évênement');

        return $this->redirectToRoute('event_show', [
            'id' => $event->getId(),
        ]);
    }

    #[Route('/create', name: 'event_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    #[IsGranted(EventVoter::CAN_CREATE, null)]
    public function create(Request $request): RedirectResponse|Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->eventFactory->create($form, $event, $this->getCurrentUser());

                $this->addFlash('success', 'Vous avez créé un nouvel évênement');

                return $this->redirectToRoute('event_show', [
                    'id' => $event->getId(),
                ]);
            } else {
                $this->addFlash('danger', 'Veuillez remplir tous les champs obligatoires');
            }
        }

        return $this->render('event/create.html.twig', [
            'formView' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'event_edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    #[IsGranted(EventVoter::CAN_EDIT, 'event')]
    public function edit(Event $event, Request $request): RedirectResponse|Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->eventFactory->edit($form, $event);
                $this->addFlash('success', 'Vous avez modifié votre évênement avec succés');

                return $this->redirectToRoute('event_show', [
                    'id' => $event->getId(),
                ]);
            } else {
                $this->addFlash('danger', 'Veuillez remplir tous les champs obligatoires');
            }
        }

        return $this->render('event/update.html.twig', [
            'formView' => $form->createView(),
            'event' => $event,

        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/delete/{id}', name: 'event_delete', methods: [Request::METHOD_DELETE])]
    #[IsGranted(EventVoter::CAN_DELETE, 'event')]
    public function delete(Event $event): RedirectResponse
    {
        $this->sender->sendDeleteTransports($event);

        $this->em->remove($event);
        $this->em->flush();

        $this->addFlash('success', "La suppression de l'évênement a réussie");

        return $this->redirectToRoute('event');
    }

    #[Route('/picture/delete/{id}', name: 'event_picture_delete', methods: [Request::METHOD_DELETE])]
    public function deleteImage(Picture $picture, Request $request, PictureService $pictureService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($this->isCsrfTokenValid('delete'.$picture->getId(), $data['_token'])) {
            $pictureService->handleDelete($picture);

            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }

    #[Route('/picture/order/{value}/{id}', name: 'event_picture_order', methods: [Request::METHOD_GET])]
    public function changePicturePriority($value, $id, PictureRepository $pictureRepository): Response
    {
        $picture = $pictureRepository->find($id);
        $picture->setOrderPriority($value);

        $this->em->flush();

        return new Response('true');
    }

    private function getCurrentUser(): ?User
    {
        /** @var User|null $user */
        $user = $this->getUser();

        return $user;
    }
}

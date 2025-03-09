<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Localisation;
use App\Entity\Participation;
use App\Entity\Picture;
use App\Entity\User;
use App\Factory\PictureFactory;
use App\Form\EventType;
use App\Repository\CategoryRepository;
use App\Repository\EventGroupRepository;
use App\Repository\EventRepository;
use App\Repository\ParticipationRepository;
use App\Repository\PictureRepository;
use App\Tools\TagService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/event')]
class EventController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected SluggerInterface $slugger,
        protected TagService $tag,
        protected MailerInterface $mailer,
        private PictureFactory $pictureFactory,
    ) {
    }

    #[Route('/', name: 'event', methods: [Request::METHOD_POST, Request::METHOD_GET])]
    public function index(EventRepository $eventRepository, CategoryRepository $categoryRepository, Request $request): JsonResponse|Response
    {
        /* nombre d'évenements par page */
        $limit = 12;

        /* on recupère le numero de la page en cours pour la pagination */
        $page = (int) $request->query->get('page', 1);

        /* On recupère l'ordre d'affichage */
        $order = $request->get('order');

        /* On récupère les filtres catgéories passé dans la requete ajax */
        $filtersCat = $request->get('categories');

        /* On recupère la localisation */
        $localisation = $request->get('localisation');

        /* On recupère le mot clé saisie */
        $keyWord = $request->get('q');

        /* on filtres les events en fonction des filtres */
        $events = $eventRepository->findByFilters($page, $limit, $order, $filtersCat, $localisation, $keyWord);

        /* On recupère le nombre total d'events : nécéssaire pour la pagination en fonction des filtres */
        $total = $eventRepository->getCountEvent($filtersCat, $localisation);

        // On verifie que c'est une requète ajax -> si oui on met a jour le content uniquement
        if ($request->get('ajax')) {
            return new JsonResponse([
                'content' => $this->renderView('event/_content.html.twig', compact('events', 'total', 'limit', 'page', 'order')),
            ]);
        }

        /* On recupère les catgéories */
        $categories = $categoryRepository->findAll();

        return $this->render('event/index.html.twig', compact('events', 'categories', 'total', 'limit', 'page'));
    }

    #[Route('/show/{event_id}', name: 'event_show', methods: [Request::METHOD_GET])]
    public function show($event_id, ParticipationRepository $participationRepository, EventRepository $eventRepository, PictureRepository $pictureRepository): RedirectResponse|Response
    {
        $event = $eventRepository->findOneBy([
            'id' => $event_id,
        ]);
        if (! $event) {
            $this->addFlash('warning', "L'évênement demandé n'existe pas");

            return $this->redirectToRoute('event');
        }

        $pictures = $pictureRepository->findBy(['event' => $event_id], ['orderPriority' => 'DESC']);

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

        return $this->render('event/show.html.twig', [
            'event' => $event,
            'user' => $user,
            'pictures' => $pictures,
            'isOnEvent' => $isOnEvent,
            'countView' => $event->getCountViews(),
        ]);
    }

    #[Route('/category/{category_id}', name: 'event_category', methods: [Request::METHOD_GET])]
    public function category($category_id, CategoryRepository $categoryRepository): RedirectResponse|Response
    {
        $category = $categoryRepository->find($category_id);
        if (! $category) {
            $this->addFlash('warning', "La Catégorie demandée n'existe pas");

            return $this->redirectToRoute('event');
        }

        return $this->render('event/category.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/group/{group_id}', name: 'event_group', methods: [Request::METHOD_GET])]
    public function group($group_id, EventGroupRepository $eventGroupRepository): RedirectResponse|Response
    {
        $eventGroup = $eventGroupRepository->find($group_id);
        if (! $eventGroup) {
            $this->addFlash('warning', "Le groupe demandé n'existe pas");

            return $this->redirectToRoute('event');
        }

        return $this->render('event/group.html.twig', [
            'eventGroup' => $eventGroup,
        ]);
    }

    #[Route('/participate/{event_id}', name: 'event_participate', methods: [Request::METHOD_GET])]
    public function participate($event_id, EventRepository $eventRepository, ParticipationRepository $participationRepository)
    {
        // recuperer l'event
        $event = $eventRepository->find($event_id);
        if (! $event) {
            $this->addFlash('warning', "L'évênement demandé n'existe pas");

            return $this->redirectToRoute('event');
        }

        // recuperer l'id du user connecté
        $user = $this->getUser();
        if (! $user) {
            $this->addFlash('warning', 'Connectez-vous pour participer à cet évênement');

            return $this->redirectToRoute('app_login');
        }

        // Verifie si le user participe déjà à cet event
        $participations = $participationRepository->findBy([
            'user' => $user->getId(),
            'event' => $event->getId(),
        ]);
        if (! empty($participations)) {
            $this->addFlash('warning', 'Vous participez déjà à cet évênement');

            return $this->redirectToRoute('event_show', [
                'event_id' => $event_id,
            ]);
        }

        // rajouter une ligne dans la table participation
        $participation = new Participation();
        $participation->setEvent($event)
            ->setUser($user)
            ->setAddedAt(new \DateTime());
        $this->em->persist($participation);
        $this->em->flush();


        $email = new TemplatedEmail();
        $email->from(new Address('admin@gmail.com', 'Admin'))
            ->subject("Participation à l'évênement : ".$event->getTitle())
            ->to($user->getEmail())
            ->htmlTemplate('emails/participation_event.html.twig')
            ->context([
                'user' => $user,
                'event' => $event,
            ]);
        $this->mailer->send($email);



        // redirige avec message
        $this->addFlash('success', 'Vous participez desormais à cet évênement');

        return $this->redirectToRoute('event_show', [
            'event_id' => $event_id,
        ]);
    }

    #[Route('/cancel/{event_id}', name: 'event_cancel', methods: [Request::METHOD_GET])]
    public function cancel($event_id, EventRepository $eventRepository, ParticipationRepository $participationRepository): RedirectResponse
    {
        // recuperer l'event
        $event = $eventRepository->find($event_id);
        if (! $event) {
            $this->addFlash('warning', "L'évênement demandé n'existe pas");

            return $this->redirectToRoute('event');
        }

        // recuperer l'id du user connecté
        $user = $this->getUser();
        if (! $user) {
            $this->addFlash('warning', 'Connectez-vous pour annuler votre participation à cet évênement.');

            return $this->redirectToRoute('app_login');
        }

        // si le user participe pas on le redirige
        $participation = $participationRepository->findOneBy([
            'user' => $user->getId(),
            'event' => $event->getId(),
        ]);
        if (empty($participation)) {
            $this->addFlash('warning', 'Vous ne participez pas encore à cet évênement.');

            return $this->redirectToRoute('event_show', [
                'event_id' => $event_id,
            ]);
        }

        // on supprime la participation
        $this->em->remove($participation);
        $this->em->flush();

        // redirige vers la page de l'event (avec message success)
        $this->addFlash('success', 'Vous avez annulé votre participation à cet évênement');

        return $this->redirectToRoute('event_show', [
            'event_id' => $event_id,
        ]);
    }

    #[Route('/create', name: 'event_create', methods: [Request::METHOD_GET,Request::METHOD_POST])]
    public function create(Request $request): RedirectResponse|Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (! $user) {
            $this->addFlash('warning', 'Connectez-vous pour creer un évênement.');

            return $this->redirectToRoute('app_login');
        }

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

                return $this->redirectToRoute('event_show', [
                    'event_id' => $event->getId(),
                ]);
            } else {
                $this->addFlash('danger', 'Veuillez remplir tous les champs obligatoires');
            }
        }

        return $this->render('event/create.html.twig', [
            'formView' => $form->createView(),
        ]);
    }

    #[Route('/edit/{event_id}', name: 'event_edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit($event_id, SluggerInterface $slugger, EventRepository $eventRepository, Request $request): RedirectResponse|Response
    {
        $event = $eventRepository->find($event_id);

        if (! $event) {
            throw $this->createNotFoundException("l'event demandé n'existe pas!");
        }

        $this->denyAccessUnlessGranted('CAN_EDIT', $event, "Vous n'êtes pas le createur de cet évênement, vous ne pouvez pas l'éditer");

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                foreach ($this->pictureFactory->handleFromForm($form) as $picture) {
                    $picture->setTitle($event->getTitle());
                    $event->addPicture($picture);
                }

                $event->setSlug(strtolower($slugger->slug($event->getTitle())));
                $tagCode = $this->tag->makeTagCode($event);
                $event->setTag($this->tag->createHtmlTag($tagCode, $event->getId(), $event->getTitle()));

                $this->em->persist($event);
                $this->em->flush();
                $this->addFlash('success', 'Vous avez modifié votre évênement avec succés');

                return $this->redirectToRoute('event_show', [
                    'event_id' => $event->getId(),
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

    #[Route('/delete/{event_id}', name: 'event_delete', methods: [Request::METHOD_DELETE])]
    public function delete($event_id, EventRepository $eventRepository): RedirectResponse
    {
        // recuperer l'event_id passé en param
        $event = $eventRepository->find($event_id);

        if (! $event) {
            throw $this->createNotFoundException("l'event demandé n'existe pas!");
        }

        $this->denyAccessUnlessGranted('CAN_DELETE', $event, "Vous n'êtes pas le createur de cet évênement, vous ne pouvez pas le supprimer");

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

        // envoyer un mail à chaque User qui a créé un transport sur cet event
        // envoyer un mail à chaque User qui a un ticket sur ces transports

        $email = new TemplatedEmail();
        $email->from(new Address('admin@gmail.com', 'Admin'))
            ->subject("Annulation de l'évênement : ".$event->getTitle())
            ->to(...$transportManagerMails, ...$ticketUserMails)
            ->htmlTemplate('emails/annulation_event.html.twig')
            ->context([
                'event' => $event,
            ]);
        $this->mailer->send($email);


        // traitement de la suppression
        $this->em->remove($event);
        $this->em->flush();




        $this->addFlash('success', "La suppression de l'évênement a réussie");

        return $this->redirectToRoute('event');
    }

    #[Route('/picture/delete/{id}', name: 'event_picture_delete', methods: [Request::METHOD_DELETE])]
    public function deleteImage(Picture $picture, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // on verifie si le token est valid
        if ($this->isCsrfTokenValid('delete'.$picture->getId(), $data['_token'])) {
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

    #[Route('/picture/order/{value}/{id}', name: 'event_picture_order', methods: [Request::METHOD_GET])]
    public function changePicturePriority($value, $id, PictureRepository $pictureRepository): Response
    {
        $picture = $pictureRepository->find($id);
        $picture->setOrderPriority($value);

        $this->em->flush();

        return new Response('true');
    }
}

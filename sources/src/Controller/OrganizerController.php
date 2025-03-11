<?php

namespace App\Controller;

use App\Entity\User;
use App\Files\CsvService;
use App\Form\EditPassType;
use App\Form\EditProfilType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/organizer')]
#[IsGranted('ROLE_ADMIN', message: 'Vous devez être organisateur pour accéder à cette partie du site.')]
class OrganizerController extends AbstractController
{
    public function __construct(
        protected UserPasswordHasherInterface $encoder,
        protected EntityManagerInterface $em,
        protected CsvService $csvService,
        protected TokenStorageInterface $tokenStorage
    ) {
    }

    #[Route('/profil', name: 'organizer_profil', methods: [Request::METHOD_GET])]
    public function profil(): RedirectResponse|Response
    {
        $user = $this->getUser();
        if (! $user) {
            $this->addFlash('warning', 'Connectez-vous pour accéder à votre profil.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('organizer/profil.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profil/edit', name: 'organizer_edit', methods: [Request::METHOD_PUT])]
    public function edit(Request $request): RedirectResponse|Response
    {
        $user = $this->getUser();
        if (! $user) {
            $this->addFlash('warning', 'Connectez-vous pour modifier votre profil.');

            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(EditProfilType::class, $user, ['chosen_role' => ['ROLE_ORGANIZER']]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->em->flush();
            $this->addFlash('success', 'La modification du profil est un succés.');

            return $this->redirectToRoute('organizer_profil');
        }

        $formView = $form->createView();

        return $this->render('organizer/edit.html.twig', [
            'formView' => $formView,
            'user' => $user,
        ]);
    }

    #[Route('/profil/password', name: 'organizer_edit_password', methods: [Request::METHOD_POST])]
    public function password(Request $request): RedirectResponse|Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        if (! $user) {
            $this->addFlash('warning', 'Connectez-vous pour modifier votre mot de passe.');

            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(EditPassType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $form->getData();

            if ($data['newPassword'] !== $data['newPasswordRepeat']) {
                $this->addFlash('warning', 'Les mots de passe doivent correspondre.');

                return $this->redirectToRoute('organizer_edit_password');
            }

            $user->setPassword($this->encoder->hashPassword($user, $data['newPassword']));

            $this->em->flush();
            $this->addFlash('success', 'La modification du mot de passe est un succés.');

            return $this->redirectToRoute('organizer_profil');
        }

        $formView = $form->createView();

        return $this->render('organizer/pass.html.twig', [
            'formView' => $formView,
            'user' => $user,
        ]);
    }

    #[Route('/profil/avatar/{path}', name: 'organizer_edit_avatar', methods: [Request::METHOD_PUT])]
    public function avatar($path): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $user->setPathAvatar($path);
        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse(['path' => $user->getPathAvatar()]);
    }

    #[Route('/profil/delete', name: 'organizer_delete', methods: [Request::METHOD_DELETE])]
    public function delete(SessionInterface $sessionInterface): RedirectResponse
    {
        /**
         * @var User
         */
        $user = $this->getUser();
        if (! $user) {
            $this->addFlash('success', 'Connectez-vous pour pouvoir supprimer votre compte');

            return $this->redirectToRoute('app_login');
        }

        $this->tokenStorage->setToken(null);
        $sessionInterface->invalidate();

        $this->em->remove($user);
        $this->em->flush();

        $this->addFlash('success', 'Votre compte a bien été supprimé');

        return $this->redirectToRoute('home');
    }

    #[Route('/events', name: 'organizer_events', methods: [Request::METHOD_GET])]
    public function events(EventRepository $eventRepository): RedirectResponse|Response
    {
        /**
         * @var User $user;
         */
        $user = $this->getUser();
        if (! $user) {
            $this->addFlash('success', 'Connectez-vous pour voir vos évênements');

            return $this->redirectToRoute('app_login');
        }

        // recupère les events à venir
        $events = $eventRepository->findByDateAfterNow($user->getId());

        // gestion CSV
        $fileName = $this->csvService->createEventCsv($events);

        return $this->render('organizer/events.html.twig', [
            'user' => $user,
            'events' => $events,
            'fileName' => $fileName,
        ]);
    }

    #[Route('/history', name: 'organizer_history', methods: [Request::METHOD_GET])]
    public function history(EventRepository $eventRepository): RedirectResponse|Response
    {
        /**
         * @var User $user;
         */
        $user = $this->getUser();
        if (! $user) {
            $this->addFlash('success', 'Connectez-vous pour voir vos évênements passés');

            return $this->redirectToRoute('app_login');
        }

        // recupère les events passés
        $events = $eventRepository->findByDateBeforeNow($user->getId());

        // gestion CSV
        $fileName = $this->csvService->createEventCsv($events);

        return $this->render('organizer/history.html.twig', [
            'user' => $user,
            'events' => $events,
            'fileName' => $fileName,
        ]);
    }

    #[Route('/stats', name: 'organizer_stats', methods: [Request::METHOD_GET])]
    public function stats(): RedirectResponse|Response
    {
        $user = $this->getUser();
        if (! $user) {
            $this->addFlash('success', 'Connectez-vous pour voir vos statistiques');

            return $this->redirectToRoute('app_login');
        }

        // gestion CSV
        $datas = $user->getEvents();
        $fileName = $this->csvService->createEventCsv($datas);


        return $this->render('organizer/stats.html.twig', [
            'user' => $user,
            'fileName' => $fileName,
        ]);
    }
}

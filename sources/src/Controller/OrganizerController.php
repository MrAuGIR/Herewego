<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditPassType;
use App\Form\EditProfilType;
use App\Repository\EventRepository;
use App\Service\Files\CsvService;
use App\Service\Files\Exception\CsvException;
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
#[IsGranted('ROLE_ORGANIZER', message: 'Vous devez être organisateur pour accéder à cette partie du site.')]
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
        return $this->render('organizer/profil.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/profil/edit', name: 'organizer_edit', methods: [Request::METHOD_GET, Request::METHOD_POST, Request::METHOD_PUT])]
    public function edit(Request $request): RedirectResponse|Response
    {
        $form = $this->createForm(EditProfilType::class, $this->getUser(), ['chosen_role' => ['ROLE_ORGANIZER']]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->em->flush();
            $this->addFlash('success', 'La modification du profil est un succés.');

            return $this->redirectToRoute('organizer_profil');
        }

        $formView = $form->createView();

        return $this->render('organizer/edit.html.twig', [
            'formView' => $formView,
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/profil/password', name: 'organizer_edit_password', methods: [Request::METHOD_POST])]
    public function password(Request $request): RedirectResponse|Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

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
        $this->tokenStorage->setToken(null);
        $sessionInterface->invalidate();

        $this->em->remove($this->getUser());
        $this->em->flush();

        $this->addFlash('success', 'Votre compte a bien été supprimé');

        return $this->redirectToRoute('home');
    }

    /**
     * @throws CsvException
     */
    #[Route('/events', name: 'organizer_events', methods: [Request::METHOD_GET])]
    public function events(EventRepository $eventRepository): RedirectResponse|Response
    {
        $events = $eventRepository->findByDateAfterNow($this->getUser()->getId());

        $fileName = $this->csvService->createEventCsv($events);

        return $this->render('organizer/events.html.twig', [
            'user' => $this->getUser(),
            'events' => $events,
            'fileName' => $fileName,
        ]);
    }

    /**
     * @throws CsvException
     */
    #[Route('/history', name: 'organizer_history', methods: [Request::METHOD_GET])]
    public function history(EventRepository $eventRepository): RedirectResponse|Response
    {
        $events = $eventRepository->findByDateBeforeNow($this->getUser()->getId());

        $fileName = $this->csvService->createEventCsv($events);

        return $this->render('organizer/history.html.twig', [
            'user' => $this->getUser(),
            'events' => $events,
            'fileName' => $fileName,
        ]);
    }

    /**
     * @throws CsvException
     */
    #[Route('/stats', name: 'organizer_stats', methods: [Request::METHOD_GET])]
    public function stats(): RedirectResponse|Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $fileName = $this->csvService->createEventCsv($user->getEvents()->toArray());

        return $this->render('organizer/stats.html.twig', [
            'user' => $user,
            'fileName' => $fileName,
        ]);
    }
}

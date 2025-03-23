<?php

namespace App\Controller;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Form\EditPassType;
use App\Form\EditProfilType;
use App\Repository\ParticipationRepository;
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

#[Route('/user')]
#[IsGranted('ROLE_USER', message: 'Vous devez être utilisateur classique pour accéder a cette partie du site')]
class UserController extends AbstractController
{
    public function __construct(
        protected UserPasswordHasherInterface $encoder,
        protected EntityManagerInterface $em,
        protected TokenStorageInterface $tokenStorage,
        protected UserFactory $userFactory,
    ) {
    }

    #[Route('/profile', name: 'user_profile', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function profile(): RedirectResponse|Response
    {
        $user = $this->getCurrentUser();

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'validatedTickets' => $user->countValidatedTickets()
        ]);
    }

    #[Route('/profile/edit', name: 'user_edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(Request $request): RedirectResponse|Response
    {
        $user = $this->getCurrentUser();

        $form = $this->createForm(EditProfilType::class, $user, ['chosen_role' => ['ROLE_USER']]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->em->flush();
            $this->addFlash('success', 'Profil modifié avec succés.');

            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user/edit.html.twig', [
            'formView' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/profile/password', name: 'user_edit_password', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function password(Request $request): RedirectResponse|Response
    {
        $user = $this->getCurrentUser();

        $form = $this->createForm(EditPassType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $form->getData();

            if ($data['newPassword'] !== $data['newPasswordRepeat']) {
                $this->addFlash('warning', 'Les mots de passe doivent correspondre.');

                return $this->redirectToRoute('user_edit_password');
            }
            $this->userFactory->updatePassword($data, $user);

            $this->addFlash('success', 'La modification du mot de passe est un succés.');

            return $this->redirectToRoute('user_profile');
        }

        $formView = $form->createView();

        return $this->render('user/pass.html.twig', [
            'formView' => $formView,
            'user' => $user,
        ]);
    }

    #[Route('/profile/avatar/{path}', name: 'user_edit_avatar', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function avatar($path): JsonResponse
    {
        $user = $this->getCurrentUser();
        $user->setPathAvatar($path);
        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse(['path' => $user->getPathAvatar()]);
    }

    #[Route('/profile/delete', name: 'user_delete', methods: [Request::METHOD_GET, Request::METHOD_DELETE])]
    public function delete(SessionInterface $sessionInterface): RedirectResponse
    {
        $user = $this->getCurrentUser();

        $this->tokenStorage->setToken(null);
        $sessionInterface->invalidate();

        $this->em->remove($user);
        $this->em->flush();

        $this->addFlash('success', 'Votre compte a bien été supprimé');

        return $this->redirectToRoute('home');
    }

    #[Route('/events', name: 'user_events', methods: [Request::METHOD_GET])]
    public function events(ParticipationRepository $participationRepository): RedirectResponse|Response
    {
        $user = $this->getCurrentUser();

        $participation = $participationRepository->findByDateAfterNow($user->getId());

        return $this->render('user/events.html.twig', [
            'user' => $user,
            'participations' => $participation,
        ]);
    }

    #[Route('/history', name: 'user_history', methods: [Request::METHOD_GET])]
    public function history(ParticipationRepository $participationRepository): RedirectResponse|Response
    {
        $user = $this->getCurrentUser();

        $participation = $participationRepository->findByDateBeforeNow($user->getId());

        return $this->render('user/history.html.twig', [
            'user' => $user,
            'participations' => $participation,
        ]);
    }

    private function getCurrentUser(): User
    {
        /** @var User $user */
        $user = $this->getUser();
        return $user;
    }
}

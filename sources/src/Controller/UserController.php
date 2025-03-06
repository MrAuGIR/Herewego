<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditPassType;
use App\Form\EditProfilType;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @Route("/user")
 *
 * @IsGranted("ROLE_USER", message="Vous devez être utilisateur classique pour accéder à cette partie du site.")
 */
#[Route('/user')]
#[IsGranted('ROLE_USER', message: 'Vous devez être utilisateur classique pour accéder a cette partie du site')]
class UserController extends AbstractController
{
    public function __construct(
        protected UserPasswordHasherInterface $encoder,
        protected EntityManagerInterface $em,
        protected TokenStorageInterface $tokenStorage
    ) {
    }

    #[Route('/profil', name: 'user_profil', methods: [Request::METHOD_POST])]
    public function profil(): \Symfony\Component\HttpFoundation\RedirectResponse|Response
    {
        $user = $this->getUser();
        if (! $user) {
            $this->addFlash('warning', 'Connectez-vous pour accéder à votre profil.');

            return $this->redirectToRoute('app_login');
        }

        // ! creer un Service pour gerer les stats utilisateur
        // calcul du nombre de ticket validé = donc du nombre de transport effectué : perfectible (possible par findBy() surement)
        $tickets = $user->getTickets();
        $validatedTickets = 0;
        foreach ($tickets as $ticket) {
            if ($ticket->getIsValidate()) {
                ++$validatedTickets;
            }
        }

        return $this->render('user/profil.html.twig', [
            'user' => $user,
            'validatedTickets' => $validatedTickets,
        ]);
    }

    #[Route('/profil/edit', name: 'user_edit', methods: [Request::METHOD_PUT])]
    public function edit(Request $request): \Symfony\Component\HttpFoundation\RedirectResponse|Response
    {
        /**
         * @var User
         */
        $user = $this->getUser();
        if (! $user) {
            $this->addFlash('warning', 'Connectez-vous pour modifier votre profil.');

            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(EditProfilType::class, $user, ['chosen_role' => ['ROLE_USER']]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->em->flush();
            $this->addFlash('success', 'Profil modifié avec succés.');

            return $this->redirectToRoute('user_profil');
        }

        $formView = $form->createView();

        return $this->render('user/edit.html.twig', [
            'formView' => $formView,
            'user' => $user,
        ]);
    }

    #[Route('/profil/password', name: 'user_edit_password', methods: [Request::METHOD_PUT])]
    public function password(Request $request): \Symfony\Component\HttpFoundation\RedirectResponse|Response
    {
        /**
         * @var PasswordAuthenticatedUserInterface $user
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

                return $this->redirectToRoute('user_edit_password');
            }

            $user->setPassword($this->encoder->hashPassword($user, $data['newPassword']));

            $this->em->flush();

            $this->addFlash('success', 'La modification du mot de passe est un succés.');

            return $this->redirectToRoute('user_profil');
        }

        $formView = $form->createView();

        return $this->render('user/pass.html.twig', [
            'formView' => $formView,
            'user' => $user,
        ]);
    }

    #[Route('/profil/avatar/{path}', name: 'user_edit_avatar', methods: [Request::METHOD_PUT])]
    public function avatar($path): JsonResponse
    {
        /**
         * @var User
         */
        $user = $this->getUser();
        $user->setPathAvatar($path);
        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse(['path' => $user->getPathAvatar()]);
    }

    #[Route('/profil/delete', name: 'user_delete', methods: [Request::METHOD_DELETE])]
    public function delete(SessionInterface $sessionInterface): \Symfony\Component\HttpFoundation\RedirectResponse
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

    #[Route('/events', name: 'user_events', methods: [Request::METHOD_GET])]
    public function events(ParticipationRepository $participationRepository): \Symfony\Component\HttpFoundation\RedirectResponse|Response
    {
        /**
         * @var User;
         */
        $user = $this->getUser();
        if (! $user) {
            $this->addFlash('warning', 'Connectez-vous pour voir vos participations aux évênements');

            return $this->redirectToRoute('app_login');
        }

        // recupère les participations à venir
        $participations = $participationRepository->findByDateAfterNow($user->getId());


        return $this->render('user/events.html.twig', [
            'user' => $user,
            'participations' => $participations,
        ]);
    }

    #[Route('/history', name: 'user_history', methods: [Request::METHOD_GET])]
    public function history(ParticipationRepository $participationRepository): \Symfony\Component\HttpFoundation\RedirectResponse|Response
    {
        /**
         * @var User;
         */
        $user = $this->getUser();
        if (! $user) {
            $this->addFlash('warning', 'Connectez-vous pour voir vos participations passées');

            return $this->redirectToRoute('app_login');
        }

        // recupère les participations passées
        $participations = $participationRepository->findByDateBeforeNow($user->getId());

        return $this->render('user/history.html.twig', [
            'user' => $user,
            'participations' => $participations,
        ]);
    }
}

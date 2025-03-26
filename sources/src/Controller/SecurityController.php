<?php

namespace App\Controller;

use App\Entity\User;
use App\Factory\OrganizerFactory;
use App\Factory\UserFactory;
use App\Form\RegisterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly UserFactory $userFactory,
        private readonly OrganizerFactory $organizerFactory,
    ) {
    }

    #[Route('/register', name: 'app_register')]
    public function register(): Response
    {
        return $this->render('security/register.html.twig');
    }

    #[Route('/register/user', name: 'app_register_user')]
    public function registerUser(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user, ['chosen_role' => ['ROLE_USER']]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userFactory->create($form, $user);

            $this->addFlash('success', 'Utilisateur créé, en attente de validation par l\'administration');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/register/organizer', name: 'app_register_organizer', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function registerOrganizer(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user, ['chosen_role' => ['ROLE_ORGANIZER']]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->organizerFactory->create($form, $user);

            $this->addFlash('success', 'Compte organisateur créé, en attente de validation par l\'administration');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.organizer.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route('/logout', name: 'app_logout', methods: [Request::METHOD_GET])]
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}

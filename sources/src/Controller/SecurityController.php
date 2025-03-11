<?php

namespace App\Controller;

use App\Entity\Localisation;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly UserFactory $userFactory
    ){}

    #[Route('/register', name: 'app_register')]
    public function register(): Response
    {
        return $this->render('security/register.html.twig');
    }

    #[Route('/register/user', name: 'app_register_user')]
    public function registerUser(Request $request, UserPasswordHasherInterface $encoder, EntityManagerInterface $manager): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user, ['chosen_role' => ['ROLE_USER']]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->userFactory->createUser($user);

            $this->addFlash('success', 'Utilisateur créé, en attente de validation par l\'administration');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/register/organizer', name: 'app_register_organizer', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function registerOrganizer(Request $request, UserPasswordHasherInterface $encoder, EntityManagerInterface $manager): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user, ['chosen_role' => ['ROLE_ORGANIZER']]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Localisation de l'organisateur */
            $localisation = new Localisation();
            $localisation->setAdress($request->request->get('register')['localisation']['adress'])
                        ->setCityName($request->request->get('register')['localisation']['cityName'])
                        ->setCityCp($request->request->get('register')['localisation']['cityCp'])
                        ->setCoordonneesX($request->request->get('register')['localisation']['coordonneesX'])
                        ->setCoordonneesY($request->request->get('register')['localisation']['coordonneesY']);


            $manager->persist($localisation);

            /* creation de l'organisateur */
            $hash = $encoder->hashPassword($user, $user->getPassword());
            $user->setPassword($hash)
                 ->setIsPremium(false)
                 ->setRoles(['ROLE_ORGANIZER'])
                 ->setRegisterAt(new \DateTime())
                 ->setLocalisation($localisation)
                 ->setCompanyName($request->request->get('register')['companyName'])
                 ->setSiret($request->request->get('register')['siret'])
                 ->setPathAvatar(0);

            if (! empty($request->request->get('regsiter')['webSite'])) {
                $user->setWebSite($request->request->get('regsiter')['webSite']);
            }

            $manager->persist($user);

            $manager->flush();

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
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route('/logout', name: 'app_logout', methods: [Request::METHOD_GET])]
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}

<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\City;
use App\Entity\Localisation;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    #[Route("/register", name: "app_register")]
    public function register(): Response
    {
        return $this->render('security/register.html.twig');
    }

    #[Route("/register/user", name: "app_register_user")]
    public function registerUser(Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user, ['chosen_role' => ['ROLE_USER']]);

        $form->handleRequest($request);

        //soumission du formulaire
        if ($form->isSubmitted() && $form->isValid()) {

            /*Localisation de l'utilisateur*/
            $localisation = new Localisation();
            $localisation->setAdress($request->request->get('register')['localisation']['adress'])
                         ->setCityName($request->request->get('register')['localisation']['cityName'])
                         ->setCityCp($request->request->get('register')['localisation']['cityCp'])
                         ->setCoordonneesX($request->request->get('register')['localisation']['coordonneesX'])
                         ->setCoordonneesY($request->request->get('register')['localisation']['coordonneesY']);

            $manager->persist($localisation);

            /* creation de l'organisateur*/
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash)
                ->setIsPremium(False)
                ->setRoles(['ROLE_USER'])
                ->setRegisterAt(new \DateTime())
                ->setLocalisation($localisation)
                ->setPathAvatar(0);

            $manager->persist($user);

            $manager->flush();

            $this->addFlash('success','Utilisateur créé, en attente de validation par l\'administration');
            return $this->redirectToRoute('app_login');

        }

        return $this->render('security/register.user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/register/organizer", name="app_register_organizer")
     */
    public function registerOrganizer(Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user, ['chosen_role'=>['ROLE_ORGANIZER']]);

        $form->handleRequest($request);

        //soumission du formulaire
        if ($form->isSubmitted() && $form->isValid()) {


            /*Localisation de l'organisateur */
            $localisation = new Localisation();
            $localisation->setAdress($request->request->get('register')['localisation']['adress'])
                        ->setCityName($request->request->get('register')['localisation']['cityName'])
                        ->setCityCp($request->request->get('register')['localisation']['cityCp'])
                        ->setCoordonneesX($request->request->get('register')['localisation']['coordonneesX'])
                        ->setCoordonneesY($request->request->get('register')['localisation']['coordonneesY']);
            

            $manager->persist($localisation);

            /* creation de l'organisateur*/
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash)
                 ->setIsPremium(False)
                 ->setRoles(['ROLE_ORGANIZER'])
                 ->setRegisterAt(new \DateTime())
                 ->setLocalisation($localisation)
                 ->setCompanyName($request->request->get('register')['companyName'])
                 ->setSiret($request->request->get('register')['siret'])
                 ->setPathAvatar(0);

            if(!empty($request->request->get('regsiter')['webSite'])){
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

    #[Route("/login", name: "app_login")]
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

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}

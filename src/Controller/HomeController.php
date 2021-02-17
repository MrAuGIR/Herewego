<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(): Response
    {
        return $this->render('home/login.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/register", name="register")
     */
    public function register(): Response
    {
        return $this->render('home/register.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/cgu", name="cgu")
     */
    public function cgu(): Response
    {
        return $this->render('home/cgu.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/legalNotice", name="legalNotice")
     */
    public function legaleNotice(): Response
    {
        return $this->render('home/legalNotice.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/plan", name="plan")
     */
    public function plan(): Response
    {
        return $this->render('home/plan.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }


    /**
     * @Route("/privacyPolicy", name="privacyPolicy")
     */
    public function privacyPolicy(): Response
    {

        return $this->render('home/privacyPolicy.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}

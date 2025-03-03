<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\EventRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class HomeController extends AbstractController
{
    #[Route('/', name: 'home' , methods: [Request::METHOD_POST])]
    public function index(EventRepository $eventRepository, CategoryRepository $categoryRepository, Request $request): Response
    {
        //derniers evenements créée
        $lastEvents = $eventRepository->findLast();

        //les events les plus populaires
        $MostPopularityEvents = $eventRepository->findByPopularity();

        //les catégories d'evenements
        $Categories = $categoryRepository->findAll();

        //On verifie que c'est une requète ajax -> si oui on met a jour le content uniquement
        if ($request->get('ajax')) {
            return new JsonResponse([
                'content' => $this->renderView('event/_content.html.twig', compact('events', 'total', 'limit', 'page', 'order'))
            ]);
        }

        return $this->render('home/index.html.twig', [
            'lastEvents'=> $lastEvents,
            'popularityEvents' => $MostPopularityEvents,
            'categories' => $Categories,
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/cgu', name: 'cgu', methods: [Request::METHOD_GET])]
    public function cgu(): Response
    {
        return $this->render('home/cgu.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/legal-Notice', name: 'legalNotice', methods: [Request::METHOD_GET])]
    public function legalNotice(): Response
    {
        return $this->render('home/legalNotice.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/plan', name: 'plan', methods: [Request::METHOD_GET])]
    public function plan(): Response
    {
        return $this->render('home/plan.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/privacy-Policy', name: 'privacyPolicy', methods: [Request::METHOD_GET])]
    public function privacyPolicy(): Response
    {

        return $this->render('home/privacyPolicy.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/who-are-we', name: 'whoAreWe', methods: [Request::METHOD_GET])]
    public function who(): Response
    {
        return $this->render('home/whoAreWe.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}

<?php

namespace App\Controller;

use App\Dto\EventQueryDto;
use App\Repository\CategoryRepository;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: [Request::METHOD_GET])]
    public function index(#[MapQueryString] EventQueryDto $dto,EventRepository $eventRepository, CategoryRepository $categoryRepository, Request $request): Response
    {
        if ($request->get('ajax')) {
            return new JsonResponse([
                'content' => $this->renderView('event/_content.html.twig', [
                    'events' => $eventRepository->findByFilters($dto),
                    'total' => $eventRepository->getCountEvent($dto),
                    'limit' => $dto->limit,
                    'page' => $dto->page,
                    'order' => $dto->order,
                ]),
            ]);
        }

        return $this->render('home/index.html.twig', [
            'lastEvents' => $eventRepository->findLast(),
            'popularityEvents' => $eventRepository->findByPopularity(),
            'categories' => $categoryRepository->findAll(),
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

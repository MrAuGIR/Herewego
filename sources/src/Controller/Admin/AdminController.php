<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route("/admin")]
#[IsGranted("ROLE_ADMIN", message: "404 page not found", statusCode: 404)]
class AdminController extends AbstractController
{
    #[Route("/", name: "admin", methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
 
}

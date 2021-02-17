<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FaqController extends AbstractController
{
    /**
     * @Route("/faq", name="faq_index")
     */
    public function index(): Response
    {
        return $this->render('faq/index.html.twig', [
            'controller_name' => 'FaqController',
        ]);
    }


    /**
     * @Route("/faq/question", name="faq_question")
     */
    public function question(): Response
    {
        return $this->render('faq/question.html.twig', [
            'controller_name' => 'FaqController',
        ]);
    }
}

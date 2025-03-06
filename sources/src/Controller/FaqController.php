<?php

namespace App\Controller;

use App\Form\FaqType;
use App\Entity\QuestionUser;
use App\Repository\QuestionAdminRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route("/faq", name: "faq_")]
class FaqController extends AbstractController
{
    #[Route("/", name: "index", methods: [Request::METHOD_GET])]
    public function index(QuestionAdminRepository $QuestionAdminRepository): \Symfony\Component\HttpFoundation\Response
    {

        $questions = $QuestionAdminRepository->findBy([], ['importance' => 'DESC']);


        return $this->render('faq/index.html.twig', [
            'questions' => $questions
        ]);
    }


    #[Route("/question", name: "question", methods: [Request::METHOD_POST])]
    public function question(Request $request, EntityManagerInterface $em): \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $question = new QuestionUser;

        $form = $this->createForm(FaqType::class, $question);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            if ($this->getUser()) {
                $question->setUser($this->getUser());
            }
            
            $em->persist($question);
            $em->flush();

            $this->addFlash('success', "Votre question a bien été envoyé à l'administrateur.");
            return $this->redirectToRoute('faq_index');
        }

        return $this->render('faq/question.html.twig', [
            'formView' => $form->createView()
        ]);
    }
}

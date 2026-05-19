<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\QuestionUser;
use App\Entity\User;
use App\Form\FaqType;
use App\Repository\QuestionAdminRepository;
use App\Service\Faq\QuestionUserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/faq', name: 'faq_')]
class FaqController extends AbstractController
{
    #[Route('/', name: 'index', methods: [Request::METHOD_GET])]
    public function index(QuestionAdminRepository $questionAdminRepository): Response
    {
        $questions = $questionAdminRepository->findBy([], ['importance' => 'DESC']);

        return $this->render('faq/index.html.twig', [
            'questions' => $questions,
        ]);
    }

    #[Route('/question', name: 'question', methods: [Request::METHOD_POST])]
    public function question(Request $request, QuestionUserManager $questionUserManager): RedirectResponse|Response
    {
        $question = new QuestionUser();

        $form = $this->createForm(FaqType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User|null $author */
            $author = $this->getUser();
            $questionUserManager->submit($question, $author);

            $this->addFlash('success', "Votre question a bien été envoyé à l'administrateur.");

            return $this->redirectToRoute('faq_index');
        }

        return $this->render('faq/question.html.twig', [
            'formView' => $form->createView(),
        ]);
    }
}

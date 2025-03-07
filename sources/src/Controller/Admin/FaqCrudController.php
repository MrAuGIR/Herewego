<?php

namespace App\Controller\Admin;

use App\Entity\QuestionAdmin;
use App\Entity\QuestionUser;
use App\Form\QuestionAdminType;
use App\Repository\QuestionAdminRepository;
use App\Repository\QuestionUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/faq')]
#[\Symfony\Component\Security\Http\Attribute\IsGranted("ROLE_ADMIN", message: "404 page not found", statusCode: 404)]
class FaqCrudController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected SluggerInterface $slugger,
        protected QuestionUserRepository $qUserRepo,
        protected QuestionAdminRepository $qAdminRepo
    )
    {
    }


    #[Route("/", name: "faqcrud")]
    public function index(): Response
    {
        /* On recupère les questions des utilisateurs */
        $questionsUser = $this->qUserRepo->findAll();

        return $this->render('admin/faq/index.html.twig', [
            'questionsUser' => $questionsUser,
        ]);
    }

    #[Route("/show/qUser/{id}", name: "faqcrud_qUser_show", methods: ["GET"])]
    public function show(QuestionUser $questionUser)
    {
    }


    #[Route("/delete/qUser/{id}", name: "faqcrud_qUser_delete", methods: [Request::METHOD_DELETE])]
    public function delete(QuestionUser $questionUser): RedirectResponse
    {
        if (! $questionUser) {
            $this->addFlash('danger', "la question demandé n'existe pas");

            return $this->redirectToRoute('faqcrud');
        }

        $this->em->remove($questionUser);
        $this->em->flush();

        $this->addFlash('success', 'La suppression de la question effectué');

        return $this->redirectToRoute('faqcrud');
    }


    #[Route("/liste", name: "faqcrud_qAdmin_liste", methods: [Request::METHOD_GET])]
    public function liste(): Response
    {
        /* On recupère les questions rédigé par les administrateurs */
        $questionsAdmin = $this->qAdminRepo->findAll();

        return $this->render('admin/faq/liste.html.twig', compact('questionsAdmin'));
    }

    #[Route("/create/qAdmin", name: "faqcrud_qAdmin_create", methods: [Request::METHOD_POST])]
    public function create(Request $request): RedirectResponse|Response
    {
        $questionAdmin = new QuestionAdmin();

        $form = $this->createForm(QuestionAdminType::class, $questionAdmin);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $questionAdmin->setImportance((int) $form->get('importance')->getData())
                ->setQuestion($form->get('question')->getData())
                ->setAnswer($form->get('answer')->getData());

            $this->em->persist($questionAdmin);
            $this->em->flush();

            $this->addFlash('success', 'Question ajouté');

            return $this->redirectToRoute('faqcrud_qAdmin_liste');
        }

        return $this->render('admin/faq/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route("/edit/qAdmin/{id}", name: "faqcrud_qAdmin_edit", methods: [Request::METHOD_PUT])]
    public function edit(QuestionAdmin $questionAdmin, Request $request): RedirectResponse|Response
    {
        $form = $this->createForm(QuestionAdminType::class, $questionAdmin);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $questionAdmin->setImportance((int) $form->get('importance')->getData())
                ->setQuestion($form->get('question')->getData())
                ->setAnswer($form->get('answer')->getData());

            $this->em->persist($questionAdmin);
            $this->em->flush();

            $this->addFlash('success', 'Question modifiée');

            return $this->redirectToRoute('faqcrud_qAdmin_liste');
        }

        return $this->render('admin/faq/edit.html.twig', ['form' => $form->createView()]);
    }

    #[Route("/delete/qAdmin/{id}", name: "faqcrud_qAdmin_delete", methods: [Request::METHOD_DELETE])]
    public function deleteQAdmin(QuestionAdmin $questionAdmin): RedirectResponse
    {
        if (! $questionAdmin) {
            $this->addFlash('danger', 'Question inexistante');

            return $this->redirectToRoute('faqcrud_qAdmin_liste');
        }

        $this->em->remove($questionAdmin);
        $this->em->flush();

        $this->addFlash('success', 'La suppression de la question éffectué');

        return $this->redirectToRoute('faqcrud_qAdmin_liste');
    }

    #[Route("/show/qAdmin/{id}", name: "faqcrud_qAdmin_show", methods: [Request::METHOD_GET])]
    public function showQAdmin(QuestionAdmin $questionAdmin): RedirectResponse
    {
        if (! $questionAdmin) {
            $this->addFlash('danger', 'Question inexistante');

            return $this->redirectToRoute('faqcrud_qAdmin_liste');
        }

        /* pas utilisé pour le moment */
        return $this->redirectToRoute('faqcrud_qAdmin_liste');
    }
}

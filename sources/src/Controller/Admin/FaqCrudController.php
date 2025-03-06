<?php

namespace App\Controller\Admin;

use App\Entity\QuestionAdmin;
use App\Entity\QuestionUser;
use App\Form\QuestionAdminType;
use App\Repository\QuestionAdminRepository;
use App\Repository\QuestionUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @isGranted("ROLE_ADMIN", statusCode=404, message="404 page not found")
 *
 * @Route("/admin/faq")
 */
class FaqCrudController extends AbstractController
{
    protected $em;
    protected $slugger;
    protected $qUserRepo;
    protected $qAdminRepo;

    public function __construct(EntityManagerInterface $em, SluggerInterface $slugger, QuestionUserRepository $qUserRepo, QuestionAdminRepository $qAdminRepo)
    {
        $this->em = $em;
        $this->slugger = $slugger;
        $this->qUserRepo = $qUserRepo;
        $this->qAdminRepo = $qAdminRepo;
    }

    /**
     * @Route("/", name="faqcrud")
     */
    public function index()
    {
        /* On recupère les questions des utilisateurs */
        $questionsUser = $this->qUserRepo->findAll();

        return $this->render('admin/faq/index.html.twig', [
            'questionsUser' => $questionsUser,
        ]);
    }

    /**
     * @Route("/show/qUser/{id}", name="faqcrud_qUser_show")
     */
    public function show(QuestionUser $questionUser)
    {
    }

    /**
     * @Route("/delete/qUser/{id}", name="faqcrud_qUser_delete")
     */
    public function delete(QuestionUser $questionUser)
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

    /**
     * @Route("/liste", name="faqcrud_qAdmin_liste")
     */
    public function liste()
    {
        /* On recupère les questions rédigé par les administrateurs */
        $questionsAdmin = $this->qAdminRepo->findAll();

        return $this->render('admin/faq/liste.html.twig', compact('questionsAdmin'));
    }

    /**
     * @Route("/create/qAdmin", name="faqcrud_qAdmin_create")
     */
    public function create(Request $request)
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

    /**
     * @Route("/edit/qAdmin/{id}", name="faqcrud_qAdmin_edit")
     */
    public function edit(QuestionAdmin $questionAdmin, Request $request)
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

    /**
     * @Route("/delete/qAdmin/{id}", name="faqcrud_qAdmin_delete")
     */
    public function deleteQAdmin(QuestionAdmin $questionAdmin)
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

    /**
     * @Route("/show/qAdmin/{id}", name="faqcrud_qAdmin_show")
     */
    public function showQAdmin(QuestionAdmin $questionAdmin)
    {
        if (! $questionAdmin) {
            $this->addFlash('danger', 'Question inexistante');

            return $this->redirectToRoute('faqcrud_qAdmin_liste');
        }

        /* pas utilisé pour le moment */
        return $this->redirectToRoute('faqcrud_qAdmin_liste');
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Factory\LogoFactory;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('ROLE_ADMIN', message: '404 page not found', statusCode: 404)]
#[Route('/admin/category',  name: 'admin_category_')]
class CategoryCrudController extends AbstractController
{
    public function __construct(
        protected UserPasswordHasherInterface $encoder,
        protected EntityManagerInterface $em,
        protected SluggerInterface $slugger,
        private readonly LogoFactory $logoFactory,
    ) {
    }

    #[Route('/', name: 'list', methods: [Request::METHOD_GET])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/show/{id}', name:'show', methods: [Request::METHOD_GET])]
    public function show(Category $category): Response
    {
        return $this->render('admin/category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/create', name:'create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (! empty($file = $this->logoFactory->handleFromForm($form, $category))) {
                $category->setPathLogo($file);
            }
            $category->setSlug(strtolower($this->slugger->slug($category->getName())));

            $this->em->persist($category);
            $this->em->flush();

            $this->addFlash('success', 'categorie ajouté');

            return $this->redirectToRoute('categorycrud');
        }

        return $this->render('admin/category/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', methods: [Request::METHOD_GET, Request::METHOD_PUT])]
    public function edit(Category $category, Request $request): Response
    {
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (! empty($file = $this->logoFactory->handleFromForm($form, $category))) {
                $category->setPathLogo($file);
            }

            $category->setSlug(strtolower($this->slugger->slug($category->getName())));

            $this->em->persist($category);
            $this->em->flush();

            $this->addFlash('success', 'categorie modifié');

            return $this->redirectToRoute('categorycrud');
        }

        return $this->render('admin/category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: [Request::METHOD_GET, Request::METHOD_DELETE])]
    public function delete(Category $category, Request $request): RedirectResponse
    {
        $this->em->remove($category);
        $this->em->flush();

        $this->addFlash('success', 'Category supprimé');

        return $this->redirectToRoute('categorycrud');
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[isGranted("ROLE_ADMIN", message: "404 page not found", statusCode: 404)]
#[Route("/admin/category")]
class CategoryCrudController extends AbstractController
{
    public function __construct(
        protected UserPasswordHasherInterface $encoder,
        protected EntityManagerInterface $em,
        protected SluggerInterface $slugger
    )
    {
    }

    #[Route("/", name: 'categorycrud', methods: [Request::METHOD_GET])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route("/show/{id}", name:"categorycrud_show", methods: [Request::METHOD_GET])]
    public function show(Category $category): Response
    {
        return $this->render('admin/category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route("/create", name:"categorycrud_create", methods: [Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /*gestion du logo */
            $logo = $form->get('pathLogo')->getData();

            if($logo != null){
                //on genere un nouveau nom de fichier (codé) et on rajoute son extension
                $fichier = md5(uniqid()) . '.' . $logo->guessExtension();

                // on copie le fichier dans le dossier uploads
                // 2 params (destination, fichier)
                $logo->move($this->getParameter('logo_directory'), $fichier);
                // on stock l'image dans la bdd (son nom)
                $category->setPathLogo($fichier);
            }

            $category->setName($request->request->get('category')['name'])
                ->setSlug(strtolower($this->slugger->slug($category->getName())))
                ->setColor($request->request->get('category')['color']);


            $this->em->persist($category);
            $this->em->flush();

            $this->addFlash('success', 'categorie ajouté');
            return $this->redirectToRoute('categorycrud');
        }

        return $this->render('admin/category/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route("/edit/{id}", name: "categorycrud_edit", methods: [Request::METHOD_PUT])]
    public function edit(Category $category, Request $request): Response
    {
        
        $form = $this->createForm(CategoryType::class,$category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $logo = $form->get('pathLogo')->getData();
            if($logo != null){
                //on genere un nouveau nom de fichier (codé) et on rajoute son extension
                $fichier = md5(uniqid()) . '.' . $logo->guessExtension();

                // on copie le fichier dans le dossier uploads
                // 2 params (destination, fichier)
                $logo->move($this->getParameter('logo_directory'), $fichier);

                /* Penser a supprimer les ancien fichiers  */
                unlink($this->getParameter('logo_directory') . '/' . $category->getPathLogo()); //ici je supprime le fichier

                // on stock l'image dans la bdd (son nom)
                $category->setPathLogo($fichier);
            }
            

            $category->setName($request->request->get('category')['name'])
                ->setSlug(strtolower($this->slugger->slug($category->getName())))
                ->setColor($request->request->get('category')['color']);
            
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

    #[Route("/delete", name: "categorycrud_delete", methods: [Request::METHOD_DELETE])]
    public function delete(Category $category, Request $request)
    {
        $this->em->remove($category);
        $this->em->flush();

        $this->addFlash('success', "Category supprimé");
        return $this->redirectToRoute('categorycrud');
    }

}
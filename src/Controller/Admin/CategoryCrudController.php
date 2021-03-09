<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use GuzzleHttp\Client;
use App\Entity\Localisation;
use App\Entity\User;
use App\Form\CategoryType;
use App\Form\RegisterType;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Flex\Path;

<<<<<<< HEAD

=======
>>>>>>> 7c864d5 (crud category (manque le edit))
/**
 * @isGranted("ROLE_ADMIN", statusCode=404, message="404 page not found")
 * @Route("/admin/category")
 */
class CategoryCrudController extends AbstractController
{
    protected $encoder;
    protected $em;
    protected $slugger;


    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $em, SluggerInterface $slugger)
    {
        $this->encoder = $encoder;
        $this->em = $em;
        $this->slugger = $slugger;
    }

    /**
     * @Route("/", name="categorycrud")
     */
    public function index(CategoryRepository $categoryRepository): Response
    {

        $categories = $categoryRepository->findAll();


        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/show/{id}", name="categorycrud_show")
     */
    public function show(Category $category)
    {

        return $this->render('admin/category/show.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * @Route("/create", name="categorycrud_create")
     */
    public function create(Request $request): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /*gestion du logo */
            $logo = $form->get('pathLogo')->getData();

<<<<<<< HEAD
            if($logo != null){
                //on genere un nouveau nom de fichier (codé) et on rajoute son extension
                $fichier = md5(uniqid()) . '.' . $logo->guessExtension();

                // on copie le fichier dans le dossier uploads
                // 2 params (destination, fichier)
                $logo->move($this->getParameter('logo_directory'), $fichier);
                // on stock l'image dans la bdd (son nom)
                $category->setPathLogo($fichier);
            }
=======
            //on genere un nouveau nom de fichier (codé) et on rajoute son extension
            $fichier = md5(uniqid()) . '.' . $logo->guessExtension();

            // on copie le fichier dans le dossier uploads
            // 2 params (destination, fichier)
            $logo->move(
                $this->getParameter('logo_directory'),
                $fichier
            );
            // on stock l'image dans la bdd (son nom)
            $category->setPathLogo($fichier);

>>>>>>> 7c864d5 (crud category (manque le edit))

            $category->setName($request->request->get('category')['name'])
                ->setSlug(strtolower($this->slugger->slug($category->getName())))
                ->setColor($request->request->get('category')['color']);


            $this->em->persist($category);
<<<<<<< HEAD
            $this->em->flush();

            $this->addFlash('success', 'categorie ajouté');
=======


            $this->em->flush();

            $this->addFlash('success', 'categorie modifié');
>>>>>>> 7c864d5 (crud category (manque le edit))
            return $this->redirectToRoute('categorycrud');
        }

        return $this->render('admin/category/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/edit/{id}", name="categorycrud_edit")
     */
    public function edit(Category $category, Request $request): Response
    {
        
        $form = $this->createForm(CategoryType::class,$category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
<<<<<<< HEAD
            $logo = $form->get('pathLogo')->getData();

=======
            
            /*gestion du logo */
            $logo = $form->get('logo')->getData();
            
>>>>>>> 7c864d5 (crud category (manque le edit))
            //on genere un nouveau nom de fichier (codé) et on rajoute son extension
            $fichier = md5(uniqid()) . '.' . $logo->guessExtension();

            // on copie le fichier dans le dossier uploads
            // 2 params (destination, fichier)
<<<<<<< HEAD
            $logo->move( $this->getParameter('logo_directory'),$fichier );

            /* Penser a supprimer les ancien fichiers  */
                
            unlink($this->getParameter('logo_directory').'/'. $category->getPathLogo()); //ici je supprime le fichier
               

            // on stock l'image dans la bdd (son nom)
            $category->setPathLogo($fichier);

            $category->setName($request->request->get('category')['name'])
                ->setSlug(strtolower($this->slugger->slug($category->getName())))
                ->setColor($request->request->get('category')['color']);
            
            $this->em->persist($category);
=======
            $logo->move(
                $this->getParameter('logo_directory'),
                $fichier
            );
            // on stock l'image dans la bdd (son nom)
            $category->setPathLogo($fichier);
            
            
            $category->setName($request->request->get('category')['name'])
                ->setSlug(strtolower($this->slugger->slug($category->getName())))
                ->setColor($request->request->get('category')['color']);
                

            $this->em->persist($category);

        
>>>>>>> 7c864d5 (crud category (manque le edit))
            $this->em->flush();

            $this->addFlash('success', 'categorie modifié');
            return $this->redirectToRoute('categorycrud');
        }

        return $this->render('admin/category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="categorycrud_delete")
     */
    public function delete(Category $category, Request $request)
    {

        //gerer les exceptions si organisateur inexistant
        //gerer la suppression en cascade event de l'organisateur


        $this->em->remove($category);
        $this->em->flush();

        $this->addFlash('success', "Category supprimé");
        return $this->redirectToRoute('categorycrud');
    }

}
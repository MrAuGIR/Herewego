<?php

namespace App\Controller\Admin;

use App\Entity\Localisation;
use App\Entity\User;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @isGranted("ROLE_ADMIN", statusCode=404, message="404 page not found")
 * @Route("/admin/organizer")
 */
class OrganizerCrudController extends AbstractController
{
    protected $encoder;
    protected $em;


    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $em)
    {
        $this->encoder = $encoder;
        $this->em = $em;
    }

    /**
     * @Route("/", name="organizercrud")
     */
    public function index(UserRepository $userRepository): Response
    {

        $users = $userRepository->findByRole('ROLE_ORGANIZER');


        return $this->render('admin/organizer/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/show/{id}", name="organizercrud_show")
     */
    public function show(User $user)
    {

        return $this->render('admin/organizer/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/create", name="organizercrud_create")
     */
    public function create(Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(RegisterType::class, $user, ['chosen_role' => ['ROLE_ORGANIZER']]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*Localisation de l'utilisateur*/
            $localisation = new Localisation();
            $localisation->setAdress($request->request->get('register')['localisation']['adress'])
            ->setCityName($request->request->get('register')['localisation']['cityName'])
            ->setCityCp($request->request->get('register')['localisation']['cityCp'])
            ->setCoordonneesX($request->request->get('register')['localisation']['coordonneesX'])
            ->setCoordonneesY($request->request->get('register')['localisation']['coordonneesY']);

            $this->em->persist($localisation);

            /* creation de l'organisateur*/
            $hash = $this->encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash)
                ->setIsValidate(true) // Comme c'est l'admin qui crée le user, il est validé dés le départ
                ->setIsPremium(False)
                ->setRoles(['ROLE_ORGANIZER'])
                ->setRegisterAt(new \DateTime())
                ->setLocalisation($localisation)
                ->setCompanyName($request->request->get('register')['companyName'])
                ->setSiret($request->request->get('register')['siret']);

            if (!empty($request->request->get('regsiter')['webSite'])) {
                $user->setWebSite($request->request->get('regsiter')['webSite']);
            };

            $this->em->persist($user);

            $this->em->flush();

            $this->addFlash('success', 'Organisateur enregistré');
            return $this->redirectToRoute('organizercrud');
        }

        return $this->render('admin/organizer/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/edit/{id}", name="organizercrud_edit")
     */
    public function edit(User $user, Request $request): Response
    {
        $form = $this->createForm(RegisterType::class, $user, ['chosen_role' => ['ROLE_ORGANIZER']]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*Localisation de l'utilisateur*/
            $localisation = $user->getLocalisation();
            $localisation->setAdress($request->request->get('register')['localisation']['adress'])
            ->setCityName($request->request->get('register')['localisation']['cityName'])
            ->setCityCp($request->request->get('register')['localisation']['cityCp'])
            ->setCoordonneesX($request->request->get('register')['localisation']['coordonneesX'])
            ->setCoordonneesY($request->request->get('register')['localisation']['coordonneesY']);

            $this->em->persist($localisation);

            /* creation de l'organisateur*/
            $hash = $this->encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash)
                ->setIsPremium(False)
                ->setRoles(['ROLE_ORGANIZER'])
                ->setLocalisation($localisation)
                ->setCompanyName($request->request->get('register')['companyName'])
                ->setSiret($request->request->get('register')['siret']);

            if (!empty($request->request->get('regsiter')['webSite'])) {
                $user->setWebSite($request->request->get('regsiter')['webSite']);
            };

            $this->em->persist($user);

            $this->em->flush();

            $this->addFlash('success', 'Organisateur modifié');
            return $this->redirectToRoute('organizercrud');
        }

        return $this->render('admin/organizer/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="organizercrud_delete")
     */
    public function delete(User $user, Request $request)
    {

        //gerer les exceptions si organisateur inexistant
        //gerer la suppression en cascade event de l'organisateur


        $this->em->remove($user);
        $this->em->flush();

        $this->addFlash('success', "Organisateur supprimé");
        $this->redirectToRoute('organizercrud');
    }
}

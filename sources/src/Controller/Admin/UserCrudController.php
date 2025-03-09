<?php

namespace App\Controller\Admin;

use App\Entity\Localisation;
use App\Entity\User;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/user')]
#[IsGranted('ROLE_ADMIN', message: '404 page note found', statusCode: 404)]
class UserCrudController extends AbstractController
{
    public function __construct(
        protected UserPasswordHasherInterface $encoder,
        protected EntityManagerInterface $em
    ) {
    }

    #[Route('/', name: 'usercrud', methods: [Request::METHOD_GET])]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        /* On recupère l'utilisateur */
        $user = $userRepository->find((int) $request->query->get('userId'));

        /* si on a recupérer un utilisateur, c'est qu'un action sur les checkbox a été effectué */
        if ($user) {
            $user->setIsValidate(($user->getIsValidate()) ? false : true);
            $this->em->flush();
        }

        /* On recupère tous les users */
        $users = $userRepository->findByRole('ROLE_USER');

        // On verifie que c'est une requète ajax -> si oui on met a jour le content uniquement
        if ($request->get('ajax')) {
            return new JsonResponse(['success' => 1]);
        }


        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/show/{id}', name: 'usercrud_show', methods: [Request::METHOD_GET])]
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/create', name: 'usercrud_create', methods: [Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(RegisterType::class, $user, ['chosen_role' => ['ROLE_USER']]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Localisation de l'utilisateur */
            $localisation = new Localisation();
            $localisation->setAdress($request->request->get('register')['localisation']['adress'])
                ->setCityName($request->request->get('register')['localisation']['cityName'])
                ->setCityCp($request->request->get('register')['localisation']['cityCp'])
                ->setCoordonneesX($request->request->get('register')['localisation']['coordonneesX'])
                ->setCoordonneesY($request->request->get('register')['localisation']['coordonneesY']);

            $this->em->persist($localisation);

            /* creation de l'organisateur */
            $hash = $this->encoder->hashPassword($user, $user->getPassword());
            $user->setPassword($hash)
                ->setIsValidate(true) // Comme c'est l'admin qui crée le user, il est validé dés le départ
                ->setIsPremium(false)
                ->setRoles(['ROLE_USER'])
                ->setRegisterAt(new \DateTime())
                ->setLocalisation($localisation);

            $this->em->persist($user);

            $this->em->flush();

            $this->addFlash('success', 'Utilisateur enregistré');

            return $this->redirectToRoute('usercrud');
        }

        return $this->render('admin/user/create.html.twig', [
            'form' => $form->createView(),
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/edit/{id}', name: 'usercrud_edit', methods: [Request::METHOD_PUT])]
    public function edit(User $user, Request $request): Response
    {
        $form = $this->createForm(RegisterType::class, $user, ['chosen_role' => ['ROLE_USER']]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Localisation de l'utilisateur */
            $localisation = $user->getLocalisation();
            $localisation->setAdress($request->request->get('register')['localisation']['adress'])
                ->setCityName($request->request->get('register')['localisation']['cityName'])
                ->setCityCp($request->request->get('register')['localisation']['cityCp'])
                ->setCoordonneesX($request->request->get('register')['localisation']['coordonneesX'])
                ->setCoordonneesY($request->request->get('register')['localisation']['coordonneesY']);

            $this->em->persist($localisation);

            /* creation de l'organisateur */
            $hash = $this->encoder->hashPassword($user, $user->getPassword());
            $user->setPassword($hash)
                ->setIsPremium(false)
                ->setRoles(['ROLE_USER'])
                ->setLocalisation($localisation);

            $this->em->persist($user);

            $this->em->flush();

            $this->addFlash('success', 'Utilisateur modifié');

            return $this->redirectToRoute('usercrud');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'usercrud_delete', methods: [Request::METHOD_DELETE])]
    public function delete(User $user, Request $request): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        // gerer les exceptions si utilisateur inexistant
        // gerer la suppression en cascade transport, ticket et participation de l'utilisateur


        $this->em->remove($user);
        $this->em->flush();

        $this->addFlash('success', 'Utilisateur supprimé');

        return $this->redirectToRoute('usercrud');
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/user')]
#[IsGranted('ROLE_ADMIN', message: '404 page note found', statusCode: 404)]
class UserCrudController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
        private readonly UserFactory $userFactory,
    ) {
    }

    #[Route('/', name: 'usercrud', methods: [Request::METHOD_GET])]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $user = $userRepository->find((int) $request->query->get('userId'));

        if ($user) {
            $user->setIsValidate(! $user->getIsValidate());
            $this->em->flush();
        }

        $users = $userRepository->findByRole('ROLE_USER');

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

    #[Route('/create', name: 'usercrud_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(RegisterType::class, $user, ['chosen_role' => ['ROLE_USER']]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userFactory->create($form, $user);

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
            $this->userFactory->edit($form, $user);

            $this->addFlash('success', 'Utilisateur modifié');

            return $this->redirectToRoute('usercrud');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'usercrud_delete', methods: [Request::METHOD_GET, Request::METHOD_DELETE])]
    public function delete(User $user, Request $request): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $this->em->remove($user);
        $this->em->flush();

        $this->addFlash('success', 'Utilisateur supprimé');

        return $this->redirectToRoute('usercrud');
    }
}

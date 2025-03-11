<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Factory\OrganizerFactory;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use App\Service\Organizer\Siret\ApiCheckSiret;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route('/admin/organizer', name: 'admin_')]
#[IsGranted('ROLE_ADMIN', message: '404 page not found', statusCode: 404)]
class OrganizerCrudController extends AbstractController
{
    public function __construct(
        protected UserPasswordHasherInterface $encoder,
        protected EntityManagerInterface      $em,
        private readonly OrganizerFactory     $organizerFactory,
    ) {
    }

    #[Route('/', name: 'organizer', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $user = $userRepository->find((int) $request->query->get('userId'));

        if ($user) {
            $user->setIsValidate(! $user->getIsValidate());
            $this->em->flush();
        }

        $users = $userRepository->findByRole('ROLE_ORGANIZER');

        if ($request->get('ajax')) {
            return new JsonResponse([
                'content' => $this->renderView('admin/organizer/_content.html.twig', ['users' => $users]),
            ]);
        }

        return $this->render('admin/organizer/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/show/{id}', name: 'organizer_show', methods: [Request::METHOD_GET])]
    public function show(User $user): Response
    {
        return $this->render('admin/organizer/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/create', name: 'organizer_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(RegisterType::class, $user, ['chosen_role' => ['ROLE_ORGANIZER']]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->organizerFactory->create($form,$user);

            $this->addFlash('success', 'Organisateur enregistré');

            return $this->redirectToRoute('admin_organizer');
        }

        return $this->render('admin/organizer/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'organizer_edit', methods: [Request::METHOD_GET,Request::METHOD_POST])]
    public function edit(User $user, Request $request): Response
    {
        $form = $this->createForm(RegisterType::class, $user, ['chosen_role' => ['ROLE_ORGANIZER']]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->organizerFactory->edit($form, $user);

            $this->addFlash('success', 'Organisateur modifié');

            return $this->redirectToRoute('admin_organizer');
        }

        return $this->render('admin/organizer/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'organizer_delete', methods: [Request::METHOD_DELETE])]
    public function delete(User $user, Request $request): RedirectResponse
    {
        $this->em->remove($user);
        $this->em->flush();

        $this->addFlash('success', 'Organisateur supprimé');

        return $this->redirectToRoute('admin_organizer');
    }

    #[Route('/verifySiret/{id}', name: 'organizer_check_siret', methods: [Request::METHOD_GET])]
    public function verifySiret(User $user, ApiCheckSiret $apiCheckSiret): RedirectResponse
    {
        if (!empty($siret = $user->getSiret())) {
            // siret isfac 49098556100011
            try {

                match ($apiCheckSiret->check($siret)) {
                    true => $this->addFlash('success', 'Siret trouvé dans la base de donnée externe'),
                    false => $this->addFlash('danger', 'SIRET inconnue')
                };

            } catch (\Exception|TransportExceptionInterface $e) {
                $this->addFlash('warning', 'Base de donnée externe en maintenance '.$e->getMessage());
            }

            return $this->redirectToRoute('admin_organizer_edit', ['id' => $user->getId()]);
        }

        return $this->redirectToRoute('admin_organizer_edit');
    }
}

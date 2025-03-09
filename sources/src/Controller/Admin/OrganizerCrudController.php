<?php

namespace App\Controller\Admin;

use App\Entity\Localisation;
use App\Entity\User;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/organizer')]
#[IsGranted('ROLE_ADMIN', message: '404 page not found', statusCode: 404)]
class OrganizerCrudController extends AbstractController
{
    public function __construct(
        protected UserPasswordHasherInterface $encoder,
        protected EntityManagerInterface $em
    ) {
    }

    #[Route('/', name: 'organizercrud', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        /* On recupère l'utilisateur */
        $user = $userRepository->find((int) $request->query->get('userId'));

        /* si on a recupérer un utilisateur, c'est qu'un action sur les checkbox a été effectué */
        if ($user) {
            $user->setIsValidate(! $user->getIsValidate());
            $this->em->flush();
        }

        /* On recupère tous les users */
        $users = $userRepository->findByRole('ROLE_ORGANIZER');

        // On verifie que c'est une requète ajax -> si oui on met a jour le content uniquement
        if ($request->get('ajax')) {
            return new JsonResponse([
                'content' => $this->renderView('admin/organizer/_content.html.twig', ['users' => $users]),
            ]);
        }

        return $this->render('admin/organizer/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/show/{id}', name: 'organizercrud_show', methods: [Request::METHOD_GET])]
    public function show(User $user): Response
    {
        return $this->render('admin/organizer/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/create', name: 'organizercrud_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(RegisterType::class, $user, ['chosen_role' => ['ROLE_ORGANIZER']]);

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
                ->setRoles(['ROLE_ORGANIZER'])
                ->setRegisterAt(new \DateTime())
                ->setLocalisation($localisation)
                ->setCompanyName($request->request->get('register')['companyName'])
                ->setSiret($request->request->get('register')['siret']);

            if (! empty($request->request->get('register')['webSite'])) {
                $user->setWebSite($request->request->get('register')['webSite']);
            }

            $this->em->persist($user);

            $this->em->flush();

            $this->addFlash('success', 'Organisateur enregistré');

            return $this->redirectToRoute('organizercrud');
        }

        return $this->render('admin/organizer/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'organizercrud_edit', methods: [Request::METHOD_PUT])]
    public function edit(User $user, Request $request): Response
    {
        $form = $this->createForm(RegisterType::class, $user, ['chosen_role' => ['ROLE_ORGANIZER']]);

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
                ->setRoles(['ROLE_ORGANIZER'])
                ->setLocalisation($localisation)
                ->setCompanyName($request->request->get('register')['companyName'])
                ->setSiret($request->request->get('register')['siret']);

            if (! empty($request->request->get('register')['webSite'])) {
                $user->setWebSite($request->request->get('register')['webSite']);
            }

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

    #[Route('/delete/{id}', name: 'organizercrud_delete', methods: [Request::METHOD_DELETE])]
    public function delete(User $user, Request $request): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $this->em->remove($user);
        $this->em->flush();

        $this->addFlash('success', 'Organisateur supprimé');

        return $this->redirectToRoute('organizercrud');
    }

    #[Route('/verifySiret/{id}', name: 'verifySiret', methods: [Request::METHOD_GET])]
    public function verifySiret(User $user): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        /*
         * @todo extraire dans un service
         */
        if (! empty($user->getSiret())) {
            // siret isfac 49098556100011
            $client = new CurlHttpClient(['base_uri' => 'https://entreprise.data.gouv.fr/api/sirene/v3/etablissements/']);

            try {
                $response = $client->request('GET', $user->getSiret(), [
                    'curl' => [
                        CURLOPT_CAINFO => dirname(dirname(dirname(__DIR__))).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'certificat64.cer',
                    ],
                ]);

                $code = $response->getStatusCode();

                if (200 == $code) {
                    // le siret a été trouvé
                    // echo $response->getBody();
                    $this->addFlash('success', 'Siret trouvé dans la base de donnée externe');

                    return $this->redirectToRoute('organizercrud_edit', ['id' => $user->getId()]);
                }

                if (500 == $code) {
                    $this->addFlash('warning', 'Base de donnée externe en maintenance');

                    return $this->redirectToRoute('organizercrud_edit', ['id' => $user->getId()]);
                }

                if (400 == $code) {
                    $this->addFlash('danger', 'SIRET inconnue');

                    return $this->redirectToRoute('organizercrud_edit', ['id' => $user->getId()]);
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }

            $this->addFlash('danger', 'Siret invalide');

            return $this->redirectToRoute('organizercrud_edit', ['id' => $user->getId()]);
        }

        $this->addFlash('danger', 'Siret null ou vide');

        return $this->redirectToRoute('organizercrud_edit');
    }
}

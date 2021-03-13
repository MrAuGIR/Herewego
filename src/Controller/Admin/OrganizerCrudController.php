<?php

namespace App\Controller\Admin;

use GuzzleHttp\Client;
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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Flex\Path;

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
    public function index(UserRepository $userRepository, Request $request): Response
    {
        /*On recupère l'utilisateur*/
        $user = $userRepository->find((int)$request->query->get('userId'));

        /* si on a recupérer un utilisateur, c'est qu'un action sur les checkbox a été effectué*/
        if ($user) {

            $user->setIsValidate(($user->getIsValidate()) ? false : true);
            $this->em->flush();
        }

        /*On recupère tous les users */
        $users = $userRepository->findByRole('ROLE_ORGANIZER');

        //On verifie que c'est une requète ajax -> si oui on met a jour le content uniquement
        if ($request->get('ajax')) {

            return new JsonResponse([
                'content' => $this->renderView('admin/organizer/_content.html.twig', ['users' => $users])
            ]);
        }

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

            if (!empty($request->request->get('register')['webSite'])) {
                $user->setWebSite($request->request->get('register')['webSite']);
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

            if (!empty($request->request->get('register')['webSite'])) {
                $user->setWebSite($request->request->get('register')['webSite']);
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
        return $this->redirectToRoute('organizercrud');
    }


    /**
     * @Route("/verifySiret/{id}", name="verifySiret")
     */
    public function verifySiret(User $user)
    {
        if(!empty($user->getSiret())){

            $client = new Client(['base_uri' => 'https://entreprise.data.gouv.fr/api/sirene/v3/etablissements/']);
            $response = $client->request('GET', '49098556100011',[
                'curl'=>[
                    CURLOPT_CAINFO => dirname(dirname(dirname(__DIR__))).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'certificat64.cer',
                ]
            ]);
            
            $code =  $response->getStatusCode();
            
            if($code == 200){
                //le siret a été trouvé
                //echo $response->getBody();
                $this->addFlash('success', 'Siret trouvé dans la base de donnée externe');
                return $this->redirectToRoute('organizercrud');
            }
            
            if($code == 500){

                $this->addFlash('warning', 'Base de donnée externe en maintenance');
                return $this->redirectToRoute('organizercrud');
            }

            $this->addFlash('danger', 'Siret invalide');
            return $this->redirectToRoute('organizercrud');

        }

        $this->addFlash('danger','Siret null ou vide');
        return $this->redirectToRoute('organizercrud');
    }

    

}

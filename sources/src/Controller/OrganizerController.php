<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Files\CsvService;
use App\Form\EditPassType;
use App\Form\EditProfilType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/organizer")
 * @IsGranted("ROLE_ORGANIZER", message="Vous devez être organisateur pour accéder à cette partie du site.")
 */
class OrganizerController extends AbstractController
{

    protected $encoder;
    protected $em;
    protected $csvService;
    protected $tokenStorage;

    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $em, CsvService $csvService, TokenStorageInterface $tokenStorage)
    {
        $this->encoder = $encoder;
        $this->em = $em;
        $this->csvService = $csvService;
        $this->tokenStorage = $tokenStorage;
    }
    
    /**
     * @Route("/profil", name="organizer_profil")
     */
    public function profil()
    {

        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', "Connectez-vous pour accéder à votre profil.");
            return $this->redirectToRoute('app_login');
        }

        return $this->render('organizer/profil.html.twig', [
            'user' => $user
        ]);
    }
    
    /**
     * @Route("/profil/edit", name="organizer_edit")
     */
    public function edit(Request $request)
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', "Connectez-vous pour modifier votre profil.");
            return $this->redirectToRoute('app_login');
        }

        

        $form = $this->createForm(EditProfilType::class, $user, ['chosen_role' => ['ROLE_ORGANIZER']]);

        $form->handleRequest($request);

        if($form->isSubmitted()) {

            $this->em->flush();
            $this->addFlash('success', "La modification du profil est un succés.");
            return $this->redirectToRoute('organizer_profil');
        }

        $formView = $form->createView();

        return $this->render('organizer/edit.html.twig', [
            'formView' => $formView,
            'user' => $user
        ]);
    }

    /**
     * @Route("/profil/password", name="organizer_edit_password")
     */
    public function password(Request $request)
    {
        /**
         * @var User
         */
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', "Connectez-vous pour modifier votre mot de passe.");
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(EditPassType::class);

        $form->handleRequest($request);

        if($form->isSubmitted()) {

            $data = $form->getData();

            if ($data['newPassword'] !== $data['newPasswordRepeat']) {
                $this->addFlash('warning', "Les mots de passe doivent correspondre.");
                return $this->redirectToRoute('organizer_edit_password');
            }

            $user->setPassword($this->encoder->encodePassword($user, $data['newPassword']));

            $this->em->flush();
            $this->addFlash('success', "La modification du mot de passe est un succés.");
            return $this->redirectToRoute('organizer_profil');
        }

        $formView = $form->createView();
        
        return $this->render('organizer/pass.html.twig', [
            'formView' => $formView,
            'user' => $user
        ]);
    }

    /**
     * @Route("/profil/avatar/{path}", name="organizer_edit_avatar")
     */
    public function avatar($path)
    {
        /**
         * @var User
         */
        $user = $this->getUser();
        $user->setPathAvatar($path);
        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse(['path' => $user->getPathAvatar()]);
    }

    /**
     * @Route("/profil/delete", name="organizer_delete")
     */
    public function delete(SessionInterface $sessionInterface)
    {
        /**
         * @var User
         */
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('success', "Connectez-vous pour pouvoir supprimer votre compte");
            return $this->redirectToRoute('app_login');
        }

        $this->tokenStorage->setToken(null);
        $sessionInterface->invalidate();

        $this->em->remove($user);
        $this->em->flush();

        $this->addFlash('success', "Votre compte a bien été supprimé");
        return $this->redirectToRoute('home');
    }
    
    /**
     * @Route("/events", name="organizer_events")
     */
    public function events(EventRepository $eventRepository)
    {
        /**
         * @var User;
         */
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('success', "Connectez-vous pour voir vos évênements");
            return $this->redirectToRoute('app_login');
        }

        //recupère les events à venir
        $events = $eventRepository->findByDateAfterNow($user->getId());

        //gestion CSV
        $fileName = $this->csvService->createEventCsv($events);

        return $this->render('organizer/events.html.twig', [
            'user' => $user,
            'events' => $events, 
            'fileName' => $fileName
        ]);
    }

    /**
     * @Route("/history", name="organizer_history")
     */
    public function history(EventRepository $eventRepository)
    {
        /**
         * @var User;
         */
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('success', "Connectez-vous pour voir vos évênements passés");
            return $this->redirectToRoute('app_login');
        }

        //recupère les events passés
        $events = $eventRepository->findByDateBeforeNow($user->getId());

        //gestion CSV
        $fileName = $this->csvService->createEventCsv($events);

        return $this->render('organizer/history.html.twig', [
            'user' => $user,
            'events' => $events, 
            'fileName' => $fileName
        ]);
    }

    /**
     * @Route("/stats", name="organizer_stats")
     */
    public function stats()
    {

        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('success', "Connectez-vous pour voir vos statistiques");
            return $this->redirectToRoute('app_login');
        }

        // gestion CSV
        $datas = $user->getEvents();
        $fileName = $this->csvService->createEventCsv($datas);


        return $this->render('organizer/stats.html.twig', [
            'user' => $user,
            'fileName' => $fileName
        ]);
    }

}

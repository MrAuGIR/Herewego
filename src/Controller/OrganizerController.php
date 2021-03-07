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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/organizer")
 * @IsGranted("ROLE_ORGANIZER", message="Vous devez être organisateur pour accéder à cette partie du site.")
 */
class OrganizerController extends AbstractController
{

    protected $encoder;
    protected $em;
    protected $csvService;

    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $em, CsvService $csvService)
    {
        $this->encoder = $encoder;
        $this->em = $em;
        $this->csvService = $csvService;
    }
    
    /**
     * @Route("/profil", name="organizer_profil")
     */
    public function profil()
    {

        $user = $this->getUser();
        if (!$user) {
            //! message flash
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
            //! message flash
            return $this->redirectToRoute('app_login');
        }

        

        $form = $this->createForm(EditProfilType::class, $user, ['chosen_role' => ['ROLE_ORGANIZER']]);

        $form->handleRequest($request);

        if($form->isSubmitted()) {

            $this->em->flush();
            //! message flash
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
            //! message flash
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(EditPassType::class);

        $form->handleRequest($request);

        if($form->isSubmitted()) {

            $data = $form->getData();

            if ($data['newPassword'] !== $data['newPasswordRepeat']) {
                //! message flash
                return $this->redirectToRoute('organizer_edit_password');
            }

            $user->setPassword($this->encoder->encodePassword($user, $data['newPassword']));

            $this->em->flush();
            //! message flash
            return $this->redirectToRoute('organizer_profil');
        }

        $formView = $form->createView();
        
        return $this->render('organizer/pass.html.twig', [
            'formView' => $formView,
            'user' => $user
        ]);
    }

    /**
     * @Route("/profil/delete", name="organizer_delete")
     */
    public function delete()
    {
        /**
         * @var User
         */
        $user = $this->getUser();
        if (!$user) {
            //! message flash
            return $this->redirectToRoute('app_login');
        }

        dd("traitement du delete d'un organizer OK");

        $this->em->remove($user);
        $this->em->flush();

       // Le delete d'un User est très complexe :
        // Il impacte :
        //      Event (createur)
        //      Transport (manager)
        //      Ticket (si a des tickets)
        //      Participation
        //      Question_user (à rendre NULL dans la bdd)

        // return $this->render('organizer/profil.html.twig');
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
            //! message flash
            return $this->redirectToRoute('app_login');
        }

        //recupère les events à venir
        $events = $eventRepository->findByDateAfterNow($user->getId());
        //! verifier si events ? ou bien dans le twig afficher : "aucun event" si vide ?

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
            //! message flash
            return $this->redirectToRoute('app_login');
        }

        //recupère les events passés
        $events = $eventRepository->findByDateBeforeNow($user->getId());
        //! verifier si events ? ou bien dans le twig afficher : "aucun event" si vide ?

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
            //! message flash
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

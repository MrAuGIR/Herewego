<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditPassType;
use App\Form\RegisterType;
use App\Form\EditProfilType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParticipationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 * @IsGranted("ROLE_USER", message="Vous devez être utilisateur classique pour accéder à cette partie du site.")
 */
class UserController extends AbstractController
{
    protected $encoder;
    protected $em;

    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->encoder = $encoder;
    }

    /**
     * @Route("/profil", name="user_profil")
     */
    public function profil()
    {
        /**
         * @var User
         */
        $user = $this->getUser();
        if (!$user) {
            //! message flash
            return $this->redirectToRoute('app_login');
        }

        // ! creer un Service pour gerer les stats utilisateur
        // calcul du nombre de ticket validé = donc du nombre de transport effectué : perfectible (possible par findBy() surement)
        $tickets = $user->getTickets();
        $validatedTickets = 0;
        foreach ($tickets as $ticket) {
            if ($ticket->getIsValidate()){
                $validatedTickets++;
            }
        }

        return $this->render('user/profil.html.twig', [
            'user' => $user, 
            'validatedTickets' => $validatedTickets
        ]);
    }
    
    /**
     * @Route("/profil/edit", name="user_edit")
     */
    public function edit(Request $request)
    {
        /**
         * @var User
         */
        $user = $this->getUser();
        if (!$user) {
            //! message flash
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(EditProfilType::class, $user, ['chosen_role' => ['ROLE_USER']]);

        $form->handleRequest($request);

        if($form->isSubmitted()) {

            $this->em->flush();
            //! message flash
            return $this->redirectToRoute('user_profil');
        }

        $formView = $form->createView();

        return $this->render('user/edit.html.twig', [
            'formView' => $formView,
            'user' => $user
        ]);
    }

    /**
     * @Route("/profil/password", name="user_edit_password")
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
                return $this->redirectToRoute('user_edit_password');
            }

            $user->setPassword($this->encoder->encodePassword($user, $data['newPassword']));

            $this->em->flush();
            //! message flash
            return $this->redirectToRoute('user_profil');
        }

        $formView = $form->createView();
        
        return $this->render('user/pass.html.twig', [
            'formView' => $formView,
            'user' => $user
        ]);
    }

    /**
     * @Route("/profil/delete", name="user_delete")
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

        dd("traitement du delete d'un user OK"); 

        $this->em->remove($user);
        $this->em->flush();

        //! message flash
        // return $this->redirectToRoute('home');

        // Le delete d'un User est très complexe :
        // Il impacte :
        //      Event (createur)
        //      Transport (manager)
        //      Ticket (si a des tickets)
        //      Participation
        //      Question_user (à rendre NULL dans la bdd)

    }
    
    /**
     * @Route("/events", name="user_events")
     */
    public function events(ParticipationRepository $participationRepository)
    {
        /**
         * @var User;
         */
        $user = $this->getUser();
        if (!$user) {
            //! message flash
            return $this->redirectToRoute('app_login');
        }

        //recupère les participations à venir
        $participations = $participationRepository->findByDateAfterNow($user->getId());
        
        return $this->render('user/events.html.twig', [
            'user' => $user,
            'participations' => $participations
        ]);
    }

    /**
     * @Route("/history", name="user_history")
     */
    public function history(ParticipationRepository $participationRepository)
    {
        /**
         * @var User;
         */
        $user = $this->getUser();
        if (!$user) {
            //! message flash
            return $this->redirectToRoute('app_login');
        }

        //recupère les participations passées
        $participations = $participationRepository->findByDateBeforeNow($user->getId());

        return $this->render('user/history.html.twig', [
            'user' => $user,
            'participations' => $participations
        ]);
    }
}

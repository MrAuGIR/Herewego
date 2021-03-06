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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    protected $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @Route("/user/profil", name="user_profil")
     */
    public function profil(Security $security)
    {
        /**
         * @var User
         */
        $user = $security->getUser();

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
     * @Route("/user/profil/edit", name="user_edit")
     */
    public function edit(Security $security, Request $request, EntityManagerInterface $em)
    {

        $user = $security->getUser();

        $form = $this->createForm(EditProfilType::class, $user, ['chosen_role' => ['ROLE_USER']]);

        $form->handleRequest($request);

        if($form->isSubmitted()) {

            // Changer le code postal ici en fonction de la ville ??
            // je ne sais pas si tu souhaites changer le cp dynamiquement vu qu'il n'apparait pas dans ton LocalisationType

            $em->flush();

            return $this->redirectToRoute('user_profil');
        }

        $formView = $form->createView();

        // dd($formView);

        return $this->render('user/edit.html.twig', [
            'formView' => $formView,
            'user' => $user
        ]);
    }

    /**
     * @Route("/user/profil/password", name="user_edit_password")
     */
    public function password(Security $security, Request $request, EntityManagerInterface $em)
    {
        /**
         * @var User
         */
        $user = $security->getUser();

        $form = $this->createForm(EditPassType::class);
        $form->handleRequest($request);

        if($form->isSubmitted()) {

            $data = $form->getData();

            if ($data['newPassword'] !== $data['newPasswordRepeat']) {
                return $this->redirectToRoute('user_edit_password');
            }

            $user->setPassword($this->encoder->encodePassword($user, $data['newPassword']));

            $em->flush();

            return $this->redirectToRoute('user_profil');
        }

        $formView = $form->createView();
        
        return $this->render('user/pass.html.twig', [
            'formView' => $formView,
            'user' => $user
        ]);
    }

    /**
     * @Route("/user/profil/delete", name="user_delete")
     */
    public function delete(Security $security, EntityManagerInterface $em)
    {
        /**
         * @var User
         */
        $user = $security->getUser();

        $em->remove($user);
        $em->flush();

        // Le delete d'un User est très complexe :
        // Il impacte :
        //      Event (createur)
        //      Transport (manager)
        //      Ticket (si a des tickets)
        //      Participation
        //      Question_user (à rendre NULL dans la bdd)

        dd("traitement du delete d'un user OK");
        // return $this->redirectToRoute('home');
    }
    
    /**
     * @Route("/user/events", name="user_events")
     */
    public function events(ParticipationRepository $participationRepository)
    {
        /**
         * @var User;
         */
        $user = $this->getUser();

        //recupère les participations à venir
        $participations = $participationRepository->findByDateAfterNow($user->getId());
        
        return $this->render('user/events.html.twig', [
            'user' => $user,
            'participations' => $participations
        ]);
    }

    /**
     * @Route("/user/history", name="user_history")
     */
    public function history(ParticipationRepository $participationRepository)
    {
        /**
         * @var User;
         */
        $user = $this->getUser();

        //recupère les participations passées
        $participations = $participationRepository->findByDateBeforeNow($user->getId());

        return $this->render('user/history.html.twig', [
            'user' => $user,
            'participations' => $participations
        ]);
    }


}

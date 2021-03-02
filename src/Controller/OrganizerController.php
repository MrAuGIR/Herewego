<?php

namespace App\Controller;

use App\Form\EditPassType;
use App\Form\EditProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class OrganizerController extends AbstractController
{

    protected $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    
    /**
     * @Route("/organizer/profil", name="organizer_profil")
     */
    public function profil(Security $security)
    {

        $user = $security->getUser();

        return $this->render('organizer/profil.html.twig', [
            'user' => $user
        ]);
    }
    
    /**
     * @Route("/organizer/profil/edit", name="organizer_edit")
     */
    public function edit(Security $security, Request $request, EntityManagerInterface $em)
    {
        $user = $security->getUser();

        $form = $this->createForm(EditProfilType::class, $user, ['chosen_role' => ['ROLE_ORGANIZER']]);

        $form->handleRequest($request);

        if($form->isSubmitted()) {

            // Changer le code postal ici en fonction de la ville ??
            // je ne sais pas si tu souhaites changer le cp dynamiquement vu qu'il n'apparait pas dans ton LocalisationType

            $em->flush();

            return $this->redirectToRoute('organizer_profil');
        }

        $formView = $form->createView();

        return $this->render('organizer/edit.html.twig', [
            'formView' => $formView,
            'user' => $user
        ]);
    }

    /**
     * @Route("/organizer/profil/password", name="organizer_edit_password")
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
                return $this->redirectToRoute('organizer_edit_password');
            }

            $user->setPassword($this->encoder->encodePassword($user, $data['newPassword']));

            $em->flush();

            return $this->redirectToRoute('organizer_profil');
        }

        $formView = $form->createView();
        
        return $this->render('organizer/pass.html.twig', [
            'formView' => $formView,
            'user' => $user
        ]);
    }

    /**
     * @Route("/organizer/profil/delete", name="organizer_delete")
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

        dd("traitement du delete d'un organizer OK");
        // return $this->render('organizer/profil.html.twig');
    }
    
    /**
     * @Route("/organizer/events", name="organizer_events")
     */
    public function events()
    {
        $user = $this->getUser();

        return $this->render('organizer/events.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/organizer/history", name="organizer_history")
     */
    public function history()
    {

        $user = $this->getUser();

        return $this->render('organizer/history.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/organizer/stats", name="organizer_stats")
     */
    public function stats()
    {

        $user = $this->getUser();

        return $this->render('organizer/stats.html.twig', [
            'user' => $user
        ]);
    }

}

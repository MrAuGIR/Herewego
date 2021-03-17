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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/user")
 * @IsGranted("ROLE_USER", message="Vous devez être utilisateur classique pour accéder à cette partie du site.")
 */
class UserController extends AbstractController
{
    protected $encoder;
    protected $em;
    protected $tokenStorage;

    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->encoder = $encoder;
        $this->tokenStorage = $tokenStorage;
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
            $this->addFlash('warning', "Connectez-vous pour accéder à votre profil.");
            return $this->redirectToRoute('app_login');
        }

        // ! creer un Service pour gerer les stats utilisateur
        // calcul du nombre de ticket validé = donc du nombre de transport effectué : perfectible (possible par findBy() surement)
        $tickets = $user->getTickets();
        $validatedTickets = 0;
        foreach ($tickets as $ticket) {
            if ($ticket->getIsValidate()) {
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
            $this->addFlash('warning', "Connectez-vous pour modifier votre profil.");
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(EditProfilType::class, $user, ['chosen_role' => ['ROLE_USER']]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $this->em->flush();
            $this->addFlash('success', "Profil modifié avec succés.");
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
            $this->addFlash('warning', "Connectez-vous pour modifier votre mot de passe.");
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(EditPassType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $data = $form->getData();

            if ($data['newPassword'] !== $data['newPasswordRepeat']) {
                $this->addFlash('warning', "Les mots de passe doivent correspondre.");
                return $this->redirectToRoute('user_edit_password');
            }

            $user->setPassword($this->encoder->encodePassword($user, $data['newPassword']));

            $this->em->flush();

            $this->addFlash('success', "La modification du mot de passe est un succés.");
            return $this->redirectToRoute('user_profil');
        }

        $formView = $form->createView();

        return $this->render('user/pass.html.twig', [
            'formView' => $formView,
            'user' => $user
        ]);
    }

    /**
     * @Route("/profil/avatar/{path}", name="user_edit_avatar")
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
     * @Route("/profil/delete", name="user_delete")
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
     * @Route("/events", name="user_events")
     */
    public function events(ParticipationRepository $participationRepository)
    {
        /**
         * @var User;
         */
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', "Connectez-vous pour voir vos participations aux évênements");
            return $this->redirectToRoute('app_login');
        }

        //recupère les participations à venir
        $participations = $participationRepository->findByDateAfterNow($user->getId());

        dump($participations);

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
            $this->addFlash('warning', "Connectez-vous pour voir vos participations passées");
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

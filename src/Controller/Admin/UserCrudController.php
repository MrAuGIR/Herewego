<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
 * @isGranted("ROLE_ADMIN", statusCode=404, message="404 page not found")
 * @Route("/admin/user")
 */
class UserCrudController extends AbstractController
{
    /**
     * @Route("/", name="usercrud")
     */
    public function index(UserRepository $userRepository): Response
    {

        $users = $userRepository->findAll();


        return $this->render('admin/user/users.html.twig', [
            'users'=>$users,
        ]);
    }

    /**
     * @Route("/create", name="usercrud_create")
     */
    public function create(): Response
    {

        return $this->render('admin/user.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

}
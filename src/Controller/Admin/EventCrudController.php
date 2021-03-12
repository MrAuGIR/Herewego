<?php

namespace App\Controller\Admin;

use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
* @isGranted("ROLE_ADMIN", statusCode=404, message="404 page not found")
* @Route("/admin/event")
*/
class EventCrudController extends AbstractController
{

    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $em, SluggerInterface $slugger)
    {
        $this->encoder = $encoder;
        $this->em = $em;
        $this->slugger = $slugger;
    }


    /**
     * @Route("/", name="eventcrud")
     */
    public function index(EventRepository $eventRepository): Response
    {

        $events = $eventRepository->findAll();


        return $this->render('admin/event/index.html.twig', [
            'events' => $events,
        ]);
    }

}
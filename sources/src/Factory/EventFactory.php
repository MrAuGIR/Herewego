<?php

namespace App\Factory;

use App\Entity\Event;
use App\Entity\User;
use App\Tools\TagService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class EventFactory
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected SluggerInterface $slugger,
        protected TagService $tag,
        private readonly PictureFactory $pictureFactory,
    ) {
    }

    public function create(FormInterface $form, Event $event, User $user): void
    {
        $event->setTag('pro')
            ->setCreatedAt(new \DateTime())
            ->setUser($user)
        ;

        $this->handleFromForm($form, $event);
    }

    public function edit(FormInterface $form, Event $event): void
    {
        $this->handleFromForm($form, $event);
    }

    public function handleFromForm(FormInterface $form, Event $event): void
    {
        foreach ($this->pictureFactory->handleFromForm($form) as $picture) {
            $picture->setTitle($event->getTitle());
            $event->addPicture($picture);
        }

        $event->setSlug(strtolower($this->slugger->slug($event->getTitle())));

        $this->em->persist($event);
        $this->em->flush();
    }
}

<?php

namespace App\EventListener;

use App\Entity\Event;
use App\Tools\TagService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: 'App\Entity\Event')]
readonly class EventTagListener
{
    public function __construct(
        private TagService $tagService,
    ) {
    }

    public function postPersist(Event $event, LifecycleEventArgs $args): void
    {
        $entityManager = $args->getObjectManager();
        $tagCode = $this->tagService->makeTagCode($event);

        $event->setTag($this->tagService->createHtmlTag($tagCode, $event->getId(), $event->getTitle()));

        $entityManager->persist($event);
        $entityManager->flush();
    }
}

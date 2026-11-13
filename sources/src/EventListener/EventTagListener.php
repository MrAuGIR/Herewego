<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Event;
use App\Tools\TagService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: 'App\Entity\Event')]
readonly class EventTagListener
{
    public function __construct(
        private TagService $tagService,
    ) {
    }

    public function postPersist(Event $event, PostPersistEventArgs $args): void
    {
        $entityManager = $args->getObjectManager();
        $tagCode = $this->tagService->makeTagCode($event);

        $event->setTag($this->tagService->createHtmlTag($tagCode, $event->getId(), $event->getTitle()));

        $entityManager->persist($event);
        $entityManager->flush();
    }
}

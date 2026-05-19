<?php

declare(strict_types=1);

namespace App\Service\Event;

use App\Entity\Event;
use App\Entity\Picture;
use App\Entity\User;
use App\Service\Mail\Sender;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Encapsule les opérations de persistance autour d'un Event.
 *
 * Sort la logique métier et l'accès direct à l'EntityManager du contrôleur
 * (SRP / DIP).
 */
final readonly class EventManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private Sender $sender,
    ) {
    }

    /**
     * Incrémente le compteur de vues de l'évènement.
     */
    public function registerView(Event $event): void
    {
        $event->setCountViews($event->getCountViews() + 1);
        $this->em->flush();
    }

    /**
     * Supprime un évènement et notifie l'organisateur.
     *
     * @throws TransportExceptionInterface
     */
    public function delete(Event $event, ?User $user): void
    {
        $this->sender->send($event, Sender::EVENT_DELETE, $user);

        $this->em->remove($event);
        $this->em->flush();
    }

    /**
     * Met à jour l'ordre d'affichage d'une image.
     */
    public function changePicturePriority(Picture $picture, int $priority): void
    {
        $picture->setOrderPriority($priority);
        $this->em->flush();
    }
}

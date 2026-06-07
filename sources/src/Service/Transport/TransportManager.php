<?php

declare(strict_types=1);

namespace App\Service\Transport;

use App\Entity\Event;
use App\Entity\Ticket;
use App\Entity\Transport;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Encapsule les opérations de persistance autour des transports et tickets.
 *
 * Sort la logique métier et l'accès direct à l'EntityManager du contrôleur
 * (SRP / DIP).
 */
final readonly class TransportManager
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function create(Transport $transport, Event $event, User $user): void
    {
        $transport->setUser($user)
            ->setEvent($event)
            ->setRemainingPlace($transport->getTotalPlace())
            ->setCreatedAt(new \DateTime());

        $this->em->persist($transport);
        $this->em->flush();
    }

    public function save(Transport $transport): void
    {
        $this->em->persist($transport);
        $this->em->flush();
    }

    public function delete(Transport $transport): void
    {
        $this->em->remove($transport);
        $this->em->flush();
    }

    /**
     * Annule (supprime) un ticket et restitue les places réservées.
     */
    public function cancelTicket(Ticket $ticket): void
    {
        $transport = $ticket->getTransport();

        if ($ticket->getIsValidate()) {
            $transport->setRemainingPlace($transport->getRemainingPlace() + $ticket->getCountPlaces());
            $this->em->persist($transport);
        }

        $this->em->remove($ticket);
        $this->em->flush();
    }

    /**
     * Refuse un ticket validé et restitue les places réservées.
     */
    public function declineTicket(Ticket $ticket): void
    {
        $transport = $ticket->getTransport();

        if ($ticket->getIsValidate()) {
            $transport->setRemainingPlace($transport->getRemainingPlace() + $ticket->getCountPlaces());
            $this->em->persist($transport);
        }

        $ticket->setIsValidate(false);
        $this->em->persist($ticket);
        $this->em->flush();
    }
}

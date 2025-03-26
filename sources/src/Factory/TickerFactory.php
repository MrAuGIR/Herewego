<?php

namespace App\Factory;

use App\Entity\Ticket;
use App\Entity\Transport;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

readonly class TickerFactory
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function createFromRequest(Transport $transport, Ticket $ticket, User $user): void
    {
        $ticket->setAskedAt(new \DateTime('now'))
            ->setTransport($transport)
            ->setUser($user)
        ;

        $this->em->persist($ticket);
        $this->em->flush();
    }

    public function validTicket(Transport $transport, Ticket $ticket): bool
    {
        if (($transport->getRemainingPlace() >= $ticket->getCountPlaces()) && ! $ticket->getIsValidate()) {
            $ticket->setIsValidate(true);
            $transport->setRemainingPlace($transport->getRemainingPlace() - $ticket->getCountPlaces());
            $ticket->setValidateAt(new \DateTime());
            $this->em->persist($transport);
            $this->em->persist($ticket);
            $this->em->flush();

            return true;
        }

        return false;
    }
}

<?php

namespace App\Factory;

use App\Entity\Event;
use App\Entity\Participation;
use App\Entity\User;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class ParticipationFactory
{
    public function __construct(
        private EntityManagerInterface  $em,
        private ParticipationRepository $participationRepository
    ){}

    public function addParticipation(Event $event, User $user): void
    {
        $participation = new Participation();
        $participation->setEvent($event)
            ->setUser($user)
            ->setAddedAt(new \DateTime());
        $this->em->persist($participation);
        $this->em->flush();
    }

    public function cancelParticipation(Event $event, User $user): void
    {
        if (!empty($participation = $this->getUserParticipation($event, $user))) {
            $this->em->remove($participation);
            $this->em->flush();
        }
    }

    public function getUserParticipation(Event $event, User $user): ?Participation
    {
        return $this->participationRepository->findOneBy([
            'user' => $user->getId(),
            'event' => $event->getId(),
        ]);
    }
}
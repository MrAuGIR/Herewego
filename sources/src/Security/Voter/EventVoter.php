<?php

namespace App\Security\Voter;

use App\Entity\Event;
use App\Entity\Participation;
use App\Entity\User;
use App\Repository\ParticipationRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class EventVoter extends Voter
{

    public const VIEW = 'view';
    public const CAN_DELETE = 'CAN_DELETE';
    public const CAN_EDIT = 'CAN_EDIT';
    public const CAN_PARTICIPATE = 'CAN_PARTICIPATE';

    public const CAN_CANCEL = 'CAN_CANCEL';
    public const CREATE_TRANSPORT = 'CREATE_TRANSPORT';


    public function __construct(
        private readonly ParticipationRepository $participationRepository
    )
    {
    }

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::CAN_DELETE, self::CAN_EDIT, self::CAN_PARTICIPATE, self::CAN_CANCEL, self::CREATE_TRANSPORT])
            && $subject instanceof Event;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        if (! $user instanceof UserInterface) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        /** @var Event $event */
        $event = $subject;

        $participation = $this->findParticipation($event,$user);

        return match ($attribute) {
            self::VIEW => $user->isParticipating($event),
            self::CREATE_TRANSPORT => $user->allowCreateTransport($event),
            self::CAN_DELETE, self::CAN_EDIT => $event->getUser() === $user,
            self::CAN_PARTICIPATE => !$participation,
            self::CAN_CANCEL => !empty($participation),
            default => false,
        };
    }

    private function findParticipation(Event $event, User $user): ?Participation
    {
        return $this->participationRepository->findOneBy([
            'user' => $user->getId(),
            'event' => $event->getId(),
        ]);
    }
}

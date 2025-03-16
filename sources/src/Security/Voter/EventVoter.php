<?php

namespace App\Security\Voter;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class EventVoter extends Voter
{

    public const VIEW = 'view';
    public const CAN_DELETE = 'CAN_DELETE';
    public const CAN_EDIT = 'CAN_EDIT';

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::CAN_DELETE, self::CAN_EDIT])
            && $subject instanceof \App\Entity\Event;
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

        return match ($attribute) {
            self::VIEW => $user->isParticipating($event),
            self::CAN_DELETE, self::CAN_EDIT => $event->getUser() === $user,
            default => false,
        };
    }
}

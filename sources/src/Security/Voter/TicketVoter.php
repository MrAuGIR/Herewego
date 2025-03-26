<?php

namespace App\Security\Voter;

use App\Entity\Ticket;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TicketVoter extends Voter
{
    public const CREATE = 'create';
    public const DELETE = 'delete';
    public const EDIT = 'edit';

    public const DECLINE = 'decline';

    public function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, [self::EDIT, self::CREATE, self::DELETE, self::DECLINE]) && ($subject instanceof Ticket);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (! $user instanceof User) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        /** @var Ticket $ticket */
        $ticket = $subject;

        return match ($attribute) {
            self::CREATE => true,
            self::DELETE, self::EDIT => $ticket->getUser() === $user,
            self::DECLINE => $ticket->getTransport()->getUser() === $user,
            default => throw new \LogicException('This code should not be reached!')
        };
    }
}

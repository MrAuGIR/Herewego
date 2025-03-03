<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Ticket;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TicketVoter extends Voter
{

    const CREATE = 'create';
    const DELETE = 'delete';
    const EDIT = 'edit';

    public function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, [self::EDIT, self::CREATE, self::DELETE]) && ($subject instanceof Ticket);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
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
            default => throw new \LogicException('This code should not be reached!')
        };

    }
}

<?php

namespace App\Security\Voter;

use App\Entity\Transport;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TransportVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const CREATE = 'create';
    public const DELETE = 'delete';
    public const MANAGE = 'manage';

    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, [self::VIEW,self::EDIT, self::CREATE, self::DELETE, self::MANAGE]) && ($subject instanceof Transport);
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

        /** @var Transport $transport */
        $transport = $subject;

        return match ($attribute) {
            self::VIEW => $user->isParticipating($transport->getEvent()),
            self::MANAGE, self::DELETE, self::EDIT => $transport->getUser() === $user,
            self::CREATE => true,
            default => false,
        };
    }
}

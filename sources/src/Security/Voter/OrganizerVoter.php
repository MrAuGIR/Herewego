<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OrganizerVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const CREATE = 'create';
    public const DELETE = 'delete';
    public const MANAGE = 'manage';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::VIEW,self::EDIT, self::CREATE, self::DELETE, self::MANAGE]) && ($subject instanceof User);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if (!in_array('ROLE_ORGANIZER', $user->getRoles(), true)) {
            return false;
        }

       return true;
    }
}
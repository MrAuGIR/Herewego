<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (! $user instanceof User) {
            return;
        }

        // Bloque tant qu'un administrateur n'a pas explicitement validé le compte
        // (couvre aussi bien is_validate = false que null).
        if (true !== $user->getIsValidate()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte est en attente de validation par un administrateur.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}

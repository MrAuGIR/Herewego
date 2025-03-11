<?php

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\Form\FormInterface;

class OrganizerFactory extends UserFactory
{
    protected function persist(FormInterface $form, User $user): void
    {
        $hash = $this->encoder->hashPassword($user, $user->getPassword());
        $user->setPassword($hash)
            ->setIsPremium(false)
            ->setRoles(['ROLE_ORGANIZER'])
        ;

        $this->em->persist($user);
        $this->em->flush();
    }
}
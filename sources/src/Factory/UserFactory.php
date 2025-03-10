<?php

namespace App\Factory;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserFactory
{
    public function __construct(
        private UserPasswordHasherInterface $encoder,
        private EntityManagerInterface      $em
    ) {
    }


    public function create(FormInterface $form, User $user): void
    {
        $user->setIsValidate(true)
            ->setRegisterAt(new \DateTime())
        ;
        $this->persist($form,$user);
    }

    public function edit(FormInterface $form, User $user): void
    {
        $this->persist($form,$user);
    }

    private function persist(FormInterface $form, User $user): void
    {
        $hash = $this->encoder->hashPassword($user, $user->getPassword());
        $user->setPassword($hash)
            ->setIsPremium(false)
            ->setRoles(['ROLE_USER'])
        ;

        $this->em->persist($user);
        $this->em->flush();
    }
}
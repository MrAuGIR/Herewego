<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{

    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();

        $user->setLastname('Does')
            ->setFirstname('John')
            ->setCompanyName('herewego')
            ->setEmail('admin@herewego.com')
            ->setPhone('0616263646')
            ->setIsValidate(True)
            ->setIsPremium(True)
            ->setRegisterAt(new \DateTime())
            ->setValidatedAt(new \DateTime())
            ->setRoles(['ROLE_ADMIN']);
        
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'admin'
        ));
        
        $manager->persist($user);
        $manager->flush();
    }
}

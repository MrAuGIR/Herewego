<?php

namespace App\DataFixtures;

use App\Entity\QuestionUser;
use Faker\Factory;
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
        // $faker = Factory::create('fr_FR');
        // $admin = new User();

        // $admin->setLastname('Does')
        //     ->setFirstname('John')
        //     ->setCompanyName('herewego')
        //     ->setEmail('admin@herewego.com')
        //     ->setPhone('0616263646')
        //     ->setIsValidate(True)
        //     ->setIsPremium(True)
        //     ->setRegisterAt(new \DateTime())
        //     ->setValidatedAt(new \DateTime())
        //     ->setRoles(['ROLE_ADMIN']);
        
        // $admin->setPassword($this->passwordEncoder->encodePassword(
        //     $admin,
        //     'admin'
        // ));
        
        // $manager->persist($admin);

        // for($i=0; $i<10; $i++){
        //     $user = new User();

        //     $user->setLastname($faker->name())
        //     ->setFirstname($faker->firstName())
        //     ->setCompanyName($faker->word())
        //     ->setEmail($faker->email())
        //     ->setPhone($faker->phoneNumber())
        //     ->setIsValidate($faker->boolean(60))
        //     ->setIsPremium(false)
        //     ->setRegisterAt(new \DateTime())
        //     ->setValidatedAt(new \DateTime())
        //     ->setRoles(['ROLE_USER']);

        //     $user->setPassword($this->passwordEncoder->encodePassword(
        //         $user,
        //         'user'
        //     ));

        //     $manager->persist($user);

        //     //question utilisateur
        //     for ($j = 1; $j <= 20; $j++) {
        //         $questionUser = new QuestionUser;
        //         $questionUser->setQuestion($faker->text(250))
        //             ->setSubject($faker->text(99))
        //             ->setUser($user);
                    
        //         $manager->persist($questionUser);
        //     }


        // }
        // $manager->flush();
    }
}

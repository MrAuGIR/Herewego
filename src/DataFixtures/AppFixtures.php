<?php

namespace App\DataFixtures;


use Faker\Factory;
use App\Entity\User;
use App\Entity\Event;
use App\Entity\Ticket;
use App\Entity\Picture;
use App\Entity\Category;
use App\Entity\Transport;
use App\Entity\EventGroup;
use App\Entity\Localisation;
use App\Entity\City;
use App\Entity\QuestionUser;
use App\Entity\QuestionAdmin;
use App\Entity\SocialNetwork;
use App\Repository\CityRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    
    private $slugger;
    private $passwordEncoder;
    protected $cityRepository;

    public function __construct(SluggerInterface $slugger, UserPasswordEncoderInterface $passwordEncoder, CityRepository $cityRepository)
    {
        $this->slugger = $slugger;
        $this->passwordEncoder = $passwordEncoder;
        $this->cityRepository = $cityRepository;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        
        for($i=0; $i<30; $i++){
            
            $city = new City();
            $city->setCityName($faker->city())
            ->setCityCp($faker->numberBetween(10000, 90000));
            $manager->persist($city);
            
            $localisation = new Localisation();
            $localisation->setAdress($faker->address())
                ->setCity($city);

            $manager->persist($localisation);
        }

        // $admin = new User();
        // // aller cherche une localisation

        // $admin->setLastname('Mine')
        //     ->setFirstname('Johnad')
        //     ->setCompanyName('herewego')
        //     ->setEmail('admin@hwg.com')
        //     ->setPhone('0836656565')
        //     ->setIsValidate(True)
        //     ->setIsPremium(True)
        //     ->setRegisterAt(new \DateTime())
        //     ->setValidatedAt(new \DateTime())
        //     ->setRoles(['ROLE_ADMIN'])
        //     ->setLocalisation('passer la localisation - objet');
        
        // $admin->setPassword($this->passwordEncoder->encodePassword($admin, 'admin'));
        // // https://via.placeholder.com/100
        
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



        // // les events groupes
        // for ($eg=0; $eg < 10; $eg++) { 
        //     $eventGroup = new EventGroup;
        //     $eventGroup->setName("groupe Event $eg")
        //         ->setPathImage("https://picsum.photos/200");
        //     $manager->persist($eventGroup);
        // }

        // // les catégories
        // for ($c=0; $c < 5; $c++) { 
        //     $category = new Category;
        //     $category->setName("event category $c")
        //         ->setSlug(strtolower($this->slugger->slug($category->getName())))
        //         ->setColor("color $c")
        //         ->setPathLogo("https://picsum.photos/200");

        //     $manager->persist($category);
        // }

        // // les pictures
        // for ($p=0; $p < 50; $p++) { 
        //     $picture = new Picture;
        //     $picture->setTitle("picture title $p")
        //         ->setOrderPriority(1)
        //         ->setPath("https://picsum.photos/200");

        //     $manager->persist($picture);
        // }

        // // les social network
        // for ($sn=0; $sn < 3; $sn++) { 
        //     $socialNetwork = new SocialNetwork;
        //     $socialNetwork->setName("social network $sn")
        //         ->setPathLogo("https://picsum.photos/200");

        //     $manager->persist($socialNetwork);
        // }

        // // events
        // for ($e=0; $e < 30; $e++) { 
        //     $event = new Event;
        //     $event->setTitle("title event $e")
        //         ->setDescription($faker->text())
        //         ->setStartedAt($faker->dateTime())
        //         ->setEndedAt($faker->dateTime())
        //         ->setEmail($faker->email())
        //         ->setWebsite($faker->url())
        //         ->setPhone($faker->phoneNumber())
        //         ->setCountViews(0)
        //         ->setSlug(strtolower($this->slugger->slug($event->getTitle())))
        //         ->setTag("tag-" . $event->getSlug())
        //         ->setCreatedAt($faker->dateTime());

        //     $manager->persist($event);
        // }

        // // transport
        // for ($t=0; $t < 30; $t++) { 
        //     $transport = new Transport;
        //     $transport->setGoStartedAt($faker->dateTime())
        //         ->setGoEndedAt($faker->dateTime())
        //         ->setReturnStartedAt($faker->dateTime())
        //         ->setReturnEndedAt($faker->dateTime())
        //         ->setCreatedAt($faker->dateTime())
        //         ->setPlacePrice($faker->numberBetween(5, 30))
        //         ->setTotalPlace($faker->numberBetween(2, 5))
        //         ->setRemainingPlace($faker->numberBetween(0, 2))
        //         ->setCommentary("comment $t-".$faker->text());

        //     $manager->persist($transport);
        // }

        

        // //question administrateur
        // for($i=1; $i<=20; $i++){
        //     $questionAdmin = new QuestionAdmin();
        //     $questionAdmin->setQuestion($faker->text(255))
        //         ->setAnswer($faker->text(255))
        //         ->setImportance($faker->numberBetween(1,10));

        //     $manager->persist($questionAdmin);
        // }

        // //tickets
        // /*
        // for($i=1; $i<=20; $i++){
        //     $ticket = new Ticket();
        //     $ticket->setAskedAt($faker->dateTime())
        //         ->setCountPlaces($faker->numberBetween(1,4))
        //         ->setCommentary($faker->text(155))
        //         ->setIsValidate($faker->boolean(40))
        //         ->setValidateAt($faker->dateTime());

        //     $manager->persist($ticket);
        // }*/

        $manager->flush();
    }
}

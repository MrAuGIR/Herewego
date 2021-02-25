<?php

namespace App\DataFixtures;


use Faker\Factory;
use App\Entity\City;
use App\Entity\User;
use App\Entity\Event;
use App\Entity\Ticket;
use App\Entity\Picture;
use App\Entity\Category;
use App\Entity\Transport;
use App\Entity\EventGroup;
use App\Entity\Localisation;
use App\Entity\Participation;
use App\Entity\QuestionUser;
use App\Entity\QuestionAdmin;
use App\Entity\SocialNetwork;
use App\Repository\CityRepository;
use Bezhanov\Faker\Provider\Commerce;
use Bluemmb\Faker\PicsumPhotosProvider;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    
    private $slugger;
    private $passwordEncoder;

    public function __construct(SluggerInterface $slugger, UserPasswordEncoderInterface $passwordEncoder, CityRepository $cityRepository)
    {
        $this->slugger = $slugger;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new \Bezhanov\Faker\Provider\Commerce($faker));
        $faker->addProvider(new \Bluemmb\Faker\PicsumPhotosProvider($faker));

        function makeLocalisation($faker, $manager) {
            $city = new City();
            $city->setCityName($faker->city())
            ->setCityCp($faker->numberBetween(10000, 90000));
            $manager->persist($city);
            
            $localisation = new Localisation();
            $localisation->setAdress($faker->address())
                ->setCity($city);
            $manager->persist($localisation);
            return $localisation;
        }
    

        $admin = new User();
        $localisation = makeLocalisation($faker, $manager);

        $admin->setLastname('Mine')
            ->setFirstname('Johnad')
            ->setCompanyName('herewego')
            ->setEmail('admin@hwg.com')
            ->setPhone('0836656565')
            ->setIsValidate(True)
            ->setIsPremium(True)
            ->setRegisterAt(new \DateTime())
            ->setValidatedAt(new \DateTime())
            ->setRoles(['ROLE_ADMIN'])
            ->setLocalisation($localisation)
            ->setPassword($this->passwordEncoder->encodePassword($admin, 'password'));            
        $manager->persist($admin);


        for($i=0; $i<10; $i++){

            $user = new User();
            $localisation = makeLocalisation($faker, $manager);
            $user->setLastname($faker->name())
            ->setFirstname($faker->firstName())
            ->setEmail($faker->email())
            ->setPhone($faker->phoneNumber())
            ->setIsValidate($faker->boolean(60))
            ->setIsPremium(false)
            ->setRegisterAt(new \DateTime())
            ->setValidatedAt(new \DateTime())
            ->setRoles(['ROLE_USER'])
            ->setPathAvatar('https://via.placeholder.com/100')
            ->setLocalisation($localisation)
            ->setPassword($this->passwordEncoder->encodePassword($user, 'password'));
            $manager->persist($user);

            $question = new QuestionUser();
            $question->setQuestion($faker->sentence(). " ?")
                ->setSubject($faker->words(3, true))
                ->setUser($user);
            $manager->persist($question);
            
        }

            

        for($i=1; $i<=20; $i++){
            $questionAdmin = new QuestionAdmin();
            $questionAdmin->setQuestion($faker->text(255))
                ->setAnswer($faker->text(255))
                ->setImportance($faker->numberBetween(1,10));

            $manager->persist($questionAdmin);
        }

        for ($c=1; $c < 4; $c++) { 
            $category = new Category;
            $category->setName($faker->department())
                ->setSlug(strtolower($this->slugger->slug($category->getName())))
                ->setColor($faker->colorName())
                ->setPathLogo("https://via.placeholder.com/50");
            $manager->persist($category);
            
            
            for ($e=1; $e < 8; $e++) { 
                
                $organizer = new User();
                $localisation = makeLocalisation($faker, $manager);
                $organizer->setLastname($faker->name())
                ->setFirstname($faker->firstName())
                ->setCompanyName($faker->word())
                ->setEmail($faker->email())
                ->setPhone($faker->phoneNumber())
                ->setIsValidate($faker->boolean(60))
                ->setIsPremium(false)
                ->setRegisterAt(new \DateTime())
                ->setValidatedAt(new \DateTime())
                ->setRoles(['ROLE_ORGANIZER'])
                ->setPathAvatar('https://via.placeholder.com/100')
                ->setPassword($this->passwordEncoder->encodePassword($organizer, 'password'))
                ->setSiret("siret-".$faker->numberBetween(10000, 99999))
                ->setCompanyName($faker->company())
                ->setLocalisation($localisation)
                ->setWebSite($faker->url());
                $manager->persist($organizer);
                
                $localisation = makeLocalisation($faker, $manager);

                for ($i=0; $i < mt_rand(1, 3); $i++) { 
                    
                    $event = new Event;
                    $event->setTitle("event $e ".$faker->words(3, true))
                    ->setDescription($faker->text())
                    ->setStartedAt($faker->dateTime())
                    ->setEndedAt($faker->dateTime())
                    ->setEmail($faker->email())
                    ->setWebsite($faker->url())
                    ->setPhone($faker->phoneNumber())
                    ->setCountViews(0)
                    ->setSlug(strtolower($this->slugger->slug($event->getTitle())))
                    ->setTag("tag-" . $event->getSlug())
                    ->setCreatedAt($faker->dateTime())
                    ->setInstagramLink($faker->url()."-insta")
                    ->setFacebookLink($faker->url()."-fb")
                    ->setTwitterLink($faker->url()."-twt")
                    ->setUser($organizer)
                    ->setLocalisation($localisation)
                    ->setCategory($category);            
                    $manager->persist($event);
    
                    $picture = new Picture;
                    $picture->setTitle("ma picture de ".$event->getTitle())
                        ->setOrderPriority(1)
                        ->setPath($faker->imageUrl())
                        ->setEvent($event);
                    $manager->persist($picture);

                    for ($t=0; $t < 5; $t++) { 
                        $user = new User();
                        $localisation = makeLocalisation($faker, $manager);
                        $user->setLastname($faker->name())
                        ->setFirstname($faker->firstName())
                        ->setEmail($faker->email())
                        ->setPhone($faker->phoneNumber())
                        ->setIsValidate($faker->boolean(60))
                        ->setIsPremium(false)
                        ->setRegisterAt(new \DateTime())
                        ->setValidatedAt(new \DateTime())
                        ->setRoles(['ROLE_USER'])
                        ->setPathAvatar('https://via.placeholder.com/100')
                        ->setLocalisation($localisation)
                        ->setPassword($this->passwordEncoder->encodePassword($user, 'password'));
                        $manager->persist($user);

                        $question = new QuestionUser();
                        $question->setQuestion($faker->words(7, true). " ?")
                            ->setSubject($faker->words(3, true))
                            ->setUser($user);
                        $manager->persist($question);

                        $localisationS = makeLocalisation($faker, $manager);
                        $localisationR = makeLocalisation($faker, $manager);

                        $transport = new Transport;
                        $transport->setGoStartedAt($faker->dateTime())
                            ->setGoEndedAt($faker->dateTime())
                            ->setReturnStartedAt($faker->dateTime())
                            ->setReturnEndedAt($faker->dateTime())
                            ->setCreatedAt($faker->dateTime())
                            ->setPlacePrice($faker->numberBetween(5, 30))
                            ->setTotalPlace($faker->numberBetween(2, 5))
                            ->setRemainingPlace($faker->numberBetween(0, 2))
                            ->setCommentary("comment $t-".$faker->text())
                            ->setEvent($event)
                            ->setUser($user)
                            ->setLocalisationStart($localisationS)
                            ->setLocalisationReturn($localisationR);

                        $manager->persist($transport);
                    }
                }                
            }
        }      

        //tickets        
        for($i=1; $i<=20; $i++){
            $ticket = new Ticket();
            $ticket->setAskedAt($faker->dateTime())
                ->setCountPlaces($faker->numberBetween(1,4))
                ->setCommentary($faker->text(155))
                ->setIsValidate($faker->boolean(40))
                ->setValidateAt($faker->dateTime());

            $manager->persist($ticket);
        }

        for($i=1; $i<=20; $i++){
            $participation = new Participation();
            $participation->setAddedAt($faker->dateTime());
            $manager->persist($participation);
        }

        $manager->flush();
    }
}

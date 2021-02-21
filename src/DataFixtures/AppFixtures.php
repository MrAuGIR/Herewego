<?php

namespace App\DataFixtures;


use Faker\Factory;
use App\Entity\Event;
use App\Entity\Picture;
use App\Entity\Category;
use App\Entity\EventGroup;
use App\Entity\SocialNetwork;
use App\Entity\Transport;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    // obligé de creer le construct, les fonctions ne sont pas liées à des routes !!
    protected $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $faker = Factory::create('fr_FR');

        // les events groupes
        for ($eg=0; $eg < 10; $eg++) { 
            $eventGroup = new EventGroup;
            $eventGroup->setName("groupe Event $eg")
                ->setPathImage("https://picsum.photos/200");
            $manager->persist($eventGroup);
        }

        // les catégories
        for ($c=0; $c < 5; $c++) { 
            $category = new Category;
            $category->setName("event category $c")
                ->setSlug(strtolower($this->slugger->slug($category->getName())))
                ->setColor("color $c")
                ->setPathLogo("https://picsum.photos/200");

            $manager->persist($category);
        }

        // les pictures
        for ($p=0; $p < 50; $p++) { 
            $picture = new Picture;
            $picture->setTitle("picture title $p")
                ->setOrderPriority(1)
                ->setPath("https://picsum.photos/200");

            $manager->persist($picture);
        }

        // les social network
        for ($sn=0; $sn < 3; $sn++) { 
            $socialNetwork = new SocialNetwork;
            $socialNetwork->setName("social network $sn")
                ->setPathLogo("https://picsum.photos/200");

            $manager->persist($socialNetwork);
        }

        // events
        for ($e=0; $e < 30; $e++) { 
            $event = new Event;
            $event->setTitle("title event $e")
                ->setDescription($faker->text())
                ->setStartedAt($faker->dateTime())
                ->setEndedAt($faker->dateTime())
                ->setEmail($faker->email())
                ->setWebsite($faker->url())
                ->setPhone($faker->phoneNumber())
                ->setCountViews(0)
                ->setSlug(strtolower($this->slugger->slug($event->getTitle())))
                ->setTag("tag-" . $event->getSlug())
                ->setCreatedAt($faker->dateTime());

            $manager->persist($event);
        }

        // transport
        for ($t=0; $t < 30; $t++) { 
            $transport = new Transport;
            $transport->setGoStartedAt($faker->dateTime())
                ->setGoEndedAt($faker->dateTime())
                ->setReturnStartedAt($faker->dateTime())
                ->setReturnEndedAt($faker->dateTime())
                ->setCreatedAt($faker->dateTime())
                ->setPlacePrice($faker->numberBetween(5, 30))
                ->setTotalPlace($faker->numberBetween(2, 5))
                ->setRemainingPlace($faker->numberBetween(0, 2))
                ->setCommentary("comment $t-".$faker->text());

            $manager->persist($transport);
        }

        $manager->flush();
    }
}

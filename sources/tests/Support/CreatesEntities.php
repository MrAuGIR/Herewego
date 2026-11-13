<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Localisation;
use App\Entity\Participation;
use App\Entity\Picture;
use App\Entity\QuestionAdmin;
use App\Entity\QuestionUser;
use App\Entity\Ticket;
use App\Entity\Transport;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Crée et persiste des entités cohérentes dans les tests qui bootent le kernel.
 * Chaque test tourne dans une transaction roll-backée (dama/doctrine-test-bundle).
 */
trait CreatesEntities
{
    protected function em(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * @param list<string> $roles
     */
    protected function createUser(string $email = 'user@test.fr', array $roles = ['ROLE_USER'], bool $validated = true, string $password = 'password'): User
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail($email)
            ->setRoles($roles)
            ->setLastname('Durand')
            ->setFirstname('Alice')
            ->setRegisterAt(new \DateTime('-1 month'))
            ->setIsValidate($validated)
            ->setLocalisation($this->createLocalisation());
        $user->setPassword($hasher->hashPassword($user, $password));

        $this->em()->persist($user);
        $this->em()->flush();

        return $user;
    }

    protected function createOrganizer(string $email = 'orga@test.fr'): User
    {
        return $this->createUser($email, ['ROLE_ORGANIZER'])->setCompanyName('Orga SA')->setSiret('49098556100011');
    }

    protected function createAdmin(string $email = 'admin@test.fr'): User
    {
        return $this->createUser($email, ['ROLE_ADMIN']);
    }

    protected function createCategory(string $name = 'Sport'): Category
    {
        $category = (new Category())->setName($name)->setSlug(strtolower($name))->setColor('#123456');
        $this->em()->persist($category);
        $this->em()->flush();

        return $category;
    }

    protected function createLocalisation(string $city = 'Lyon', string $cp = '69000'): Localisation
    {
        $localisation = (new Localisation())->setAdress('1 rue de la République')->setCityName($city)->setCityCp($cp);
        $this->em()->persist($localisation);

        return $localisation;
    }

    protected function createEvent(User $organizer, ?Category $category = null, string $start = '+1 week', string $end = '+2 weeks', string $title = 'Concert test'): Event
    {
        $event = (new Event())
            ->setTitle($title)
            ->setDescription('<p>Une description suffisamment longue pour un évènement.</p>')
            ->setStartedAt(new \DateTime($start))
            ->setEndedAt(new \DateTime($end))
            ->setCreatedAt(new \DateTime())
            ->setSlug('concert-test')
            ->setTag('pro')
            ->setCategory($category ?? $this->createCategory())
            ->setLocalisation($this->createLocalisation())
            ->setUser($organizer);
        $this->em()->persist($event);
        $this->em()->flush();

        return $event;
    }

    protected function createParticipation(Event $event, User $user): Participation
    {
        $participation = (new Participation())->setEvent($event)->setUser($user)->setAddedAt(new \DateTime());
        $event->addParticipation($participation);
        $user->addParticipation($participation);
        $this->em()->persist($participation);
        $this->em()->flush();

        return $participation;
    }

    protected function createTransport(Event $event, User $manager, int $places = 4, string $goStart = '+1 week'): Transport
    {
        $transport = (new Transport())
            ->setEvent($event)
            ->setUser($manager)
            ->setLocalisationStart($this->createLocalisation('Villeurbanne', '69100'))
            ->setLocalisationReturn($this->createLocalisation('Villeurbanne', '69100'))
            ->setGoStartedAt(new \DateTime($goStart))
            ->setGoEndedAt(new \DateTime($goStart.' +2 hours'))
            ->setReturnStartedAt(new \DateTime($goStart.' +10 hours'))
            ->setReturnEndedAt(new \DateTime($goStart.' +12 hours'))
            ->setPlacePrice('10')
            ->setTotalPlace($places)
            ->setRemainingPlace($places)
            ->setCommentary('Départ parking')
            ->setCreatedAt(new \DateTime());
        $event->addTransport($transport);
        $this->em()->persist($transport);
        $this->em()->flush();

        return $transport;
    }

    protected function createTicket(Transport $transport, User $user, int $places = 1, bool $validated = false): Ticket
    {
        $ticket = (new Ticket())
            ->setTransport($transport)
            ->setUser($user)
            ->setCountPlaces($places)
            ->setAskedAt(new \DateTime())
            ->setIsValidate($validated);
        $transport->addTicket($ticket);
        $user->addTicket($ticket);
        $this->em()->persist($ticket);
        $this->em()->flush();

        return $ticket;
    }

    protected function createPicture(Event $event, string $path = 'test.jpg', int $priority = 1): Picture
    {
        $picture = (new Picture())->setPath($path)->setTitle('Photo')->setOrderPriority($priority);
        $event->addPicture($picture);
        $this->em()->persist($picture);
        $this->em()->flush();

        return $picture;
    }

    protected function createQuestionAdmin(int $importance = 1): QuestionAdmin
    {
        $question = (new QuestionAdmin())->setQuestion('Comment ça marche ?')->setAnswer('Très bien.')->setImportance($importance);
        $this->em()->persist($question);
        $this->em()->flush();

        return $question;
    }

    protected function createQuestionUser(?User $author = null): QuestionUser
    {
        $question = (new QuestionUser())->setSubject('Inscription')->setQuestion('Comment valider mon inscription rapidement ?')->setUser($author);
        $this->em()->persist($question);
        $this->em()->flush();

        return $question;
    }
}

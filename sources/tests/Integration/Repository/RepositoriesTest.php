<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Dto\EventQueryDto;
use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Repository\ParticipationRepository;
use App\Repository\TicketRepository;
use App\Repository\TransportRepository;
use App\Repository\UserRepository;
use App\Tests\Support\CreatesEntities;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\InMemoryUser;

final class RepositoriesTest extends KernelTestCase
{
    use CreatesEntities;

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private function service(string $class): object
    {
        return self::getContainer()->get($class);
    }

    public function testEventsSplitByDateForOrganizer(): void
    {
        $organizer = $this->createOrganizer();
        $upcoming = $this->createEvent($organizer, title: 'A venir');
        $past = $this->createEvent($organizer, $upcoming->getCategory(), '-2 weeks', '-1 week', 'Passé');
        $this->createEvent($this->createOrganizer('other@test.fr'), $upcoming->getCategory());

        $repo = $this->service(EventRepository::class);

        self::assertSame([$upcoming], $repo->findByDateAfterNow($organizer->getId()));
        self::assertSame([$past], $repo->findByDateBeforeNow($organizer->getId()));
    }

    public function testLastAndPopularEventsAreOrdered(): void
    {
        $organizer = $this->createOrganizer();
        $old = $this->createEvent($organizer, title: 'Ancien')->setCreatedAt(new \DateTime('-1 year'))->setCountViews(50);
        $recent = $this->createEvent($organizer, $old->getCategory(), title: 'Récent')->setCountViews(1);
        $this->em()->flush();

        $repo = $this->service(EventRepository::class);

        self::assertSame([$recent, $old], $repo->findLast());
        self::assertSame([$recent, $old], $repo->findLast($old->getCategory()));
        self::assertSame([$old, $recent], $repo->findByPopularity());
    }

    public function testFiltersByCategoryKeywordAndLocalisation(): void
    {
        $organizer = $this->createOrganizer();
        $concert = $this->createEvent($organizer, title: 'Concert de jazz');
        $match = $this->createEvent($organizer, $this->createCategory('Foot'), title: 'Match de foot');

        $repo = $this->service(EventRepository::class);

        $byCategory = new EventQueryDto(categories: [$match->getCategory()->getId()]);
        self::assertSame([$match], $repo->findByFilters($byCategory));
        self::assertSame(1, $repo->getCountEvent($byCategory));

        $byKeyword = new EventQueryDto(q: 'Concert');
        self::assertSame([$concert], $repo->findByFilters($byKeyword));
        self::assertSame(1, $repo->getCountEvent($byKeyword));

        $byCity = new EventQueryDto(localisation: 'Lyon', order: 'DESC');
        self::assertCount(2, $repo->findByFilters($byCity));
        self::assertSame(2, $repo->getCountEvent($byCity));
    }

    public function testParticipationsSplitByDate(): void
    {
        $organizer = $this->createOrganizer();
        $user = $this->createUser();
        $upcoming = $this->createEvent($organizer);
        $past = $this->createEvent($organizer, $upcoming->getCategory(), '-2 weeks', '-1 week');
        $p1 = $this->createParticipation($upcoming, $user);
        $p2 = $this->createParticipation($past, $user);

        $repo = $this->service(ParticipationRepository::class);

        self::assertSame([$p1], $repo->findByDateAfterNow($user->getId()));
        self::assertSame([$p2], $repo->findByDateBeforeNow($user->getId()));
    }

    public function testTransportsToAlertStartWithinTwoDays(): void
    {
        $organizer = $this->createOrganizer();
        $event = $this->createEvent($organizer);
        $soon = $this->createTransport($event, $organizer, goStart: '+1 day');
        $this->createTransport($event, $this->createUser(), goStart: '+10 days');
        $this->createTransport($event, $this->createUser('late@test.fr'), goStart: '-1 day');

        self::assertSame([$soon], $this->service(TransportRepository::class)->findTransportToAlert());
    }

    public function testTicketLookupByUserAndTransport(): void
    {
        $organizer = $this->createOrganizer();
        $user = $this->createUser();
        $transport = $this->createTransport($this->createEvent($organizer), $organizer);
        $ticket = $this->createTicket($transport, $user);

        $repo = $this->service(TicketRepository::class);

        self::assertSame($ticket, $repo->findOneByUserAndTransport($user, $transport));
        self::assertNull($repo->findOneByUserAndTransport($organizer, $transport));
    }

    public function testUsersByRoleAndPasswordUpgrade(): void
    {
        $user = $this->createUser();
        $organizer = $this->createOrganizer();
        $repo = $this->service(UserRepository::class);

        self::assertSame([$organizer], $repo->findByRole('ROLE_ORGANIZER'));

        $repo->upgradePassword($user, 'new-hash');
        self::assertSame('new-hash', $this->em()->find(User::class, $user->getId())?->getPassword());

        $this->expectException(UnsupportedUserException::class);
        $repo->upgradePassword(new InMemoryUser('x', 'y'), 'hash');
    }

    public function testEventTagIsGeneratedOnPersist(): void
    {
        $event = $this->createEvent($this->createOrganizer());

        $this->em()->clear();
        $reloaded = $this->em()->find(Event::class, $event->getId());

        self::assertStringContainsString('/event/show/'.$event->getId(), (string) $reloaded?->getTag());
    }
}

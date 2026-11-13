<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Event;
use App\Entity\Participation;
use App\Entity\Ticket;
use App\Entity\Transport;
use App\Entity\User;
use App\Tests\Support\SetsIds;
use PHPUnit\Framework\TestCase;

final class UserBusinessRulesTest extends TestCase
{
    use SetsIds;

    public function testParticipationIsDetectedByEventId(): void
    {
        $user = self::withId(new User(), 1);
        $event = self::withId(new Event(), 10);
        $other = self::withId(new Event(), 11);

        $user->addParticipation((new Participation())->setEvent($event));

        self::assertTrue($user->isParticipating($event));
        self::assertFalse($user->isParticipating($other));
    }

    public function testCanCreateTransportOnlyWhenParticipatingAndNotAlreadyManaging(): void
    {
        $user = self::withId(new User(), 1);
        $event = self::withId(new Event(), 10);

        self::assertFalse($user->allowCreateTransport($event), 'non participant');

        $user->addParticipation((new Participation())->setEvent($event));
        self::assertTrue($user->allowCreateTransport($event));

        $event->addTransport((new Transport())->setUser($user));
        self::assertTrue($user->alreadyManageTransport($event));
        self::assertFalse($user->allowCreateTransport($event), 'gère déjà un transport');
    }

    public function testCountValidatedTickets(): void
    {
        $user = new User();
        $user->addTicket((new Ticket())->setIsValidate(true));
        $user->addTicket((new Ticket())->setIsValidate(false));
        $user->addTicket((new Ticket())->setIsValidate(true));

        self::assertSame(2, $user->countValidatedTickets());
    }

    public function testIdentifierIsEmailAndCredentialsAreErasable(): void
    {
        $user = (new User())->setEmail('alice@test.fr');

        self::assertSame('alice@test.fr', $user->getUserIdentifier());
        self::assertSame('alice@test.fr', $user->getUsername());
        $user->eraseCredentials();
        self::assertNull($user->getSalt());
    }
}

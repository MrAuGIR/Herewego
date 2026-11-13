<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\Event;
use App\Entity\Participation;
use App\Entity\Ticket;
use App\Entity\Transport;
use App\Entity\User;
use App\Security\Voter\EventVoter;
use App\Security\Voter\TicketVoter;
use App\Security\Voter\TransportVoter;
use App\Tests\Support\SetsIds;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class VotersTest extends TestCase
{
    use SetsIds;

    private const GRANTED = VoterInterface::ACCESS_GRANTED;
    private const DENIED = VoterInterface::ACCESS_DENIED;
    private const ABSTAIN = VoterInterface::ACCESS_ABSTAIN;

    private static function token(User $user): UsernamePasswordToken
    {
        return new UsernamePasswordToken($user, 'main', $user->getRoles());
    }

    public function testEventVoter(): void
    {
        $voter = new EventVoter();
        $organizer = self::withId((new User())->setRoles(['ROLE_ORGANIZER']), 1);
        $participant = self::withId(new User(), 2);
        $stranger = self::withId(new User(), 3);
        $admin = self::withId((new User())->setRoles(['ROLE_ADMIN']), 4);
        $event = self::withId((new Event())->setUser($organizer), 10);
        $participant->addParticipation((new Participation())->setEvent($event));

        self::assertSame(self::GRANTED, $voter->vote(self::token($organizer), $event, [EventVoter::CAN_EDIT]));
        self::assertSame(self::GRANTED, $voter->vote(self::token($organizer), $event, [EventVoter::CAN_DELETE]));
        self::assertSame(self::DENIED, $voter->vote(self::token($stranger), $event, [EventVoter::CAN_EDIT]));
        self::assertSame(self::GRANTED, $voter->vote(self::token($participant), $event, [EventVoter::VIEW]));
        self::assertSame(self::GRANTED, $voter->vote(self::token($participant), $event, [EventVoter::CAN_CANCEL]));
        self::assertSame(self::DENIED, $voter->vote(self::token($participant), $event, [EventVoter::CAN_PARTICIPATE]));
        self::assertSame(self::GRANTED, $voter->vote(self::token($stranger), $event, [EventVoter::CAN_PARTICIPATE]));
        self::assertSame(self::GRANTED, $voter->vote(self::token($participant), $event, [EventVoter::CREATE_TRANSPORT]));
        self::assertSame(self::GRANTED, $voter->vote(self::token($organizer), null, [EventVoter::CAN_CREATE]));
        self::assertSame(self::DENIED, $voter->vote(self::token($stranger), null, [EventVoter::CAN_CREATE]));
        self::assertSame(self::GRANTED, $voter->vote(self::token($admin), $event, [EventVoter::CAN_DELETE]));
        self::assertSame(self::DENIED, $voter->vote(new NullToken(), $event, [EventVoter::VIEW]));
        self::assertSame(self::ABSTAIN, $voter->vote(self::token($stranger), new \stdClass(), [EventVoter::VIEW]));
    }

    public function testTicketVoter(): void
    {
        $voter = new TicketVoter();
        $manager = self::withId(new User(), 1);
        $passenger = self::withId(new User(), 2);
        $stranger = self::withId(new User(), 3);
        $admin = self::withId((new User())->setRoles(['ROLE_ADMIN']), 4);
        $ticket = (new Ticket())->setUser($passenger)->setTransport((new Transport())->setUser($manager));

        self::assertSame(self::GRANTED, $voter->vote(self::token($passenger), $ticket, [TicketVoter::DELETE]));
        self::assertSame(self::GRANTED, $voter->vote(self::token($passenger), $ticket, [TicketVoter::EDIT]));
        self::assertSame(self::DENIED, $voter->vote(self::token($stranger), $ticket, [TicketVoter::DELETE]));
        self::assertSame(self::GRANTED, $voter->vote(self::token($manager), $ticket, [TicketVoter::DECLINE]));
        self::assertSame(self::DENIED, $voter->vote(self::token($passenger), $ticket, [TicketVoter::DECLINE]));
        self::assertSame(self::GRANTED, $voter->vote(self::token($stranger), $ticket, [TicketVoter::CREATE]));
        self::assertSame(self::GRANTED, $voter->vote(self::token($admin), $ticket, [TicketVoter::DECLINE]));
        self::assertSame(self::DENIED, $voter->vote(new NullToken(), $ticket, [TicketVoter::CREATE]));
        self::assertSame(self::ABSTAIN, $voter->vote(self::token($stranger), $ticket, ['unknown']));
    }

    public function testTransportVoter(): void
    {
        $voter = new TransportVoter();
        $manager = self::withId(new User(), 1);
        $participant = self::withId(new User(), 2);
        $stranger = self::withId(new User(), 3);
        $admin = self::withId((new User())->setRoles(['ROLE_ADMIN']), 4);
        $event = self::withId(new Event(), 10);
        $participant->addParticipation((new Participation())->setEvent($event));
        $transport = (new Transport())->setUser($manager)->setEvent($event);

        foreach ([TransportVoter::MANAGE, TransportVoter::EDIT, TransportVoter::DELETE] as $attribute) {
            self::assertSame(self::GRANTED, $voter->vote(self::token($manager), $transport, [$attribute]));
            self::assertSame(self::DENIED, $voter->vote(self::token($stranger), $transport, [$attribute]));
        }
        self::assertSame(self::GRANTED, $voter->vote(self::token($participant), $transport, [TransportVoter::VIEW]));
        self::assertSame(self::DENIED, $voter->vote(self::token($stranger), $transport, [TransportVoter::VIEW]));
        self::assertSame(self::GRANTED, $voter->vote(self::token($stranger), $transport, [TransportVoter::CREATE]));
        self::assertSame(self::GRANTED, $voter->vote(self::token($admin), $transport, [TransportVoter::DELETE]));
        self::assertSame(self::DENIED, $voter->vote(new NullToken(), $transport, [TransportVoter::VIEW]));
    }
}

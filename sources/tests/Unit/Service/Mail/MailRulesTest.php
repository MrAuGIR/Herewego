<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Mail;

use App\Entity\Event;
use App\Entity\Ticket;
use App\Entity\Transport;
use App\Entity\User;
use App\Message\Handler\SendEmailMessageHandler;
use App\Message\SendEmailMessage;
use App\Service\Mail\Rules\DeleteTransportSender;
use App\Service\Mail\Rules\EventParticipationSender;
use App\Service\Mail\Rules\EventTransportSender;
use App\Service\Mail\Rules\MailerRuleInterface;
use App\Service\Mail\Sender;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class MailRulesTest extends TestCase
{
    private static function user(string $email): User
    {
        return (new User())->setEmail($email);
    }

    private static function eventWithTransport(): Event
    {
        $transport = (new Transport())->setUser(self::user('driver@test.fr'));
        $transport->addTicket((new Ticket())->setUser(self::user('passenger@test.fr')));

        return (new Event())->setTitle('Festival')->addTransport($transport);
    }

    /**
     * @return array{0: MailerInterface, 1: \ArrayObject<int, TemplatedEmail>}
     */
    private function recordingMailer(): array
    {
        /** @var \ArrayObject<int, TemplatedEmail> $sent */
        $sent = new \ArrayObject();
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->method('send')->willReturnCallback(static function (TemplatedEmail $email) use ($sent): void {
            $sent[] = $email;
        });

        return [$mailer, $sent];
    }

    public function testParticipationMailIsSentToUser(): void
    {
        [$mailer, $sent] = $this->recordingMailer();
        $rule = new EventParticipationSender($mailer, $this->createStub(LoggerInterface::class));
        $event = (new Event())->setTitle('Festival');

        self::assertTrue($rule->support(EventParticipationSender::MATCH, $event));
        self::assertFalse($rule->support('other', $event));
        self::assertFalse($rule->support(EventParticipationSender::MATCH, new Transport()));

        $rule->send($event, self::user('alice@test.fr'));

        self::assertCount(1, $sent);
        self::assertSame('alice@test.fr', $sent[0]->getTo()[0]->getAddress());
        self::assertStringContainsString('Festival', (string) $sent[0]->getSubject());
        self::assertSame('emails/participation_event.html.twig', $sent[0]->getHtmlTemplate());
    }

    public function testTransportMailIsSentToPassenger(): void
    {
        [$mailer, $sent] = $this->recordingMailer();
        $rule = new EventTransportSender($mailer, $this->createStub(LoggerInterface::class));
        $transport = (new Transport())->setEvent((new Event())->setTitle('Festival'));

        self::assertTrue($rule->support(EventTransportSender::MATCH, $transport));
        self::assertFalse($rule->support(EventTransportSender::MATCH, new Event()));

        $rule->send($transport, self::user('bob@test.fr'));

        self::assertCount(1, $sent);
        self::assertSame('emails/transport_event.html.twig', $sent[0]->getHtmlTemplate());
    }

    public function testDeleteMailTargetsDriversAndPassengers(): void
    {
        [$mailer, $sent] = $this->recordingMailer();
        $rule = new DeleteTransportSender($mailer, $this->createStub(LoggerInterface::class));
        $event = self::eventWithTransport();

        self::assertTrue($rule->support(DeleteTransportSender::DELETE_TRANSPORT, $event));

        $rule->send($event, null);

        self::assertCount(1, $sent);
        self::assertSame(
            ['driver@test.fr', 'passenger@test.fr'],
            array_map(static fn (Address $a): string => $a->getAddress(), $sent[0]->getTo()),
        );
    }

    public function testMailerFailuresAreLoggedNotThrown(): void
    {
        $mailer = $this->createStub(MailerInterface::class);
        $mailer->method('send')->willThrowException(new TransportException('SMTP down'));
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::exactly(3))->method('error');

        (new EventParticipationSender($mailer, $logger))->send((new Event())->setTitle('x'), self::user('a@test.fr'));
        (new EventTransportSender($mailer, $logger))->send((new Transport())->setEvent((new Event())->setTitle('x')), self::user('a@test.fr'));
        (new DeleteTransportSender($mailer, $logger))->send(self::eventWithTransport(), null);
    }

    public function testSenderDispatchesOnlyToSupportingRules(): void
    {
        $event = new Event();
        $user = new User();
        $matching = $this->createMock(MailerRuleInterface::class);
        $matching->method('support')->willReturn(true);
        $matching->expects(self::once())->method('send')->with($event, $user);
        $other = $this->createMock(MailerRuleInterface::class);
        $other->method('support')->willReturn(false);
        $other->expects(self::never())->method('send');

        (new Sender([$matching, $other]))->send($event, Sender::EVENT_PARTICIPATION, $user);
    }

    public function testMessageHandlerDelegatesToSender(): void
    {
        $event = new Event();
        $user = new User();
        $rule = $this->createMock(MailerRuleInterface::class);
        $rule->method('support')->willReturn(true);
        $rule->expects(self::once())->method('send')->with($event, $user);

        $message = new SendEmailMessage(new \stdClass(), 'x', null);
        $message->setObject($event);
        $message->setMatch(Sender::EVENT_PARTICIPATION);
        $message->setUser($user);

        self::assertSame($event, $message->getObject());
        self::assertSame(Sender::EVENT_PARTICIPATION, $message->getMatch());
        self::assertSame($user, $message->getUser());

        (new SendEmailMessageHandler(new Sender([$rule])))($message);
    }
}

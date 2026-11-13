<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\Tests\Support\CreatesEntities;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class SendEmailCommandTest extends KernelTestCase
{
    use CreatesEntities;

    public function testRemindersAreSentToValidatedPassengersOfImminentTransports(): void
    {
        $organizer = $this->createOrganizer();
        $event = $this->createEvent($organizer);
        $soon = $this->createTransport($event, $organizer, goStart: '+1 day');
        $this->createTicket($soon, $this->createUser('ok@test.fr'), validated: true);
        $this->createTicket($soon, $this->createUser('pending@test.fr'));
        $later = $this->createTransport($event, $this->createUser('driver@test.fr'), goStart: '+10 days');
        $this->createTicket($later, $this->createUser('later@test.fr'), validated: true);

        $tester = new CommandTester((new Application(self::bootKernel()))->find('app:send-email-transport'));
        $tester->execute([]);

        $tester->assertCommandIsSuccessful();
        self::assertStringContainsString('1 email envoyé', $tester->getDisplay());
        self::assertEmailCount(1);
        self::assertEmailAddressContains(self::getMailerMessage(), 'To', 'ok@test.fr');
    }
}

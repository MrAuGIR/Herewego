<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Event;
use App\Entity\Picture;
use App\Entity\Ticket;
use App\Entity\Transport;
use App\Factory\ParticipationFactory;
use App\Factory\TickerFactory;
use App\Factory\UserFactory;
use App\Service\Event\EventManager;
use App\Service\Faq\QuestionUserManager;
use App\Service\Files\CsvService;
use App\Service\Files\PictureService;
use App\Service\Transport\TransportManager;
use App\Tests\Support\CreatesEntities;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class DomainServicesTest extends KernelTestCase
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

    public function testParticipationLifecycle(): void
    {
        $event = $this->createEvent($this->createOrganizer());
        $user = $this->createUser();
        $factory = $this->service(ParticipationFactory::class);

        $factory->addParticipation($event, $user);
        self::assertNotNull($participation = $factory->getUserParticipation($event, $user));
        self::assertSame($user, $participation->getUser());

        $factory->cancelParticipation($event, $user);
        self::assertNull($factory->getUserParticipation($event, $user));
        $factory->cancelParticipation($event, $user); // sans effet
    }

    public function testTicketValidationConsumesPlacesOnce(): void
    {
        $organizer = $this->createOrganizer();
        $transport = $this->createTransport($this->createEvent($organizer), $organizer, places: 3);
        $factory = $this->service(TickerFactory::class);

        $ticket = (new Ticket())->setCountPlaces(2);
        $factory->createFromRequest($transport, $ticket, $this->createUser());
        self::assertNotNull($ticket->getId());
        self::assertNotNull($ticket->getAskedAt());

        self::assertTrue($factory->validTicket($transport, $ticket));
        self::assertSame(1, $transport->getRemainingPlace());
        self::assertNotNull($ticket->getValidateAt());
        self::assertFalse($factory->validTicket($transport, $ticket), 'déjà validé');

        $tooBig = $this->createTicket($transport, $this->createUser('big@test.fr'), places: 2);
        self::assertFalse($factory->validTicket($transport, $tooBig), 'plus assez de places');
    }

    public function testTransportManagerRestoresPlaces(): void
    {
        $organizer = $this->createOrganizer();
        $event = $this->createEvent($organizer);
        $manager = $this->service(TransportManager::class);

        $transport = (new Transport())
            ->setLocalisationStart($this->createLocalisation())
            ->setLocalisationReturn($this->createLocalisation())
            ->setGoStartedAt(new \DateTime('+1 day'))->setGoEndedAt(new \DateTime('+1 day'))
            ->setReturnStartedAt(new \DateTime('+2 days'))->setReturnEndedAt(new \DateTime('+2 days'))
            ->setPlacePrice('5')->setTotalPlace(4)->setCommentary('ok');
        $manager->create($transport, $event, $organizer);
        self::assertSame(4, $transport->getRemainingPlace());
        self::assertSame($organizer, $transport->getUser());

        $transport->setRemainingPlace(2);
        $declined = $this->createTicket($transport, $this->createUser(), places: 2, validated: true);
        $manager->declineTicket($declined);
        self::assertSame(4, $transport->getRemainingPlace());
        self::assertFalse($declined->getIsValidate());

        $transport->setRemainingPlace(3);
        $cancelled = $this->createTicket($transport, $this->createUser('c@test.fr'), places: 1, validated: true);
        $cancelledId = $cancelled->getId();
        $manager->cancelTicket($cancelled);
        self::assertSame(4, $transport->getRemainingPlace());
        self::assertNull($this->em()->find(Ticket::class, $cancelledId));

        $manager->save($transport->setCommentary('modifié'));
        $id = $transport->getId();
        $manager->delete($transport);
        self::assertNull($this->em()->find(Transport::class, $id));
    }

    public function testEventManager(): void
    {
        $event = $this->createEvent($this->createOrganizer());
        $picture = $this->createPicture($event);
        $manager = $this->service(EventManager::class);

        $manager->registerView($event);
        self::assertSame(1, $event->getCountViews());

        $manager->changePicturePriority($picture, 5);
        self::assertSame(5, $picture->getOrderPriority());

        $id = $event->getId();
        $manager->delete($event, null);
        self::assertNull($this->em()->find(Event::class, $id));
    }

    public function testPasswordUpdateAndQuestionSubmission(): void
    {
        $user = $this->createUser();
        $this->service(UserFactory::class)->updatePassword(['newPassword' => 'n3w-Passw0rd'], $user);
        self::assertTrue($this->service(UserPasswordHasherInterface::class)->isPasswordValid($user, 'n3w-Passw0rd'));

        $question = $this->createQuestionUser();
        $this->service(QuestionUserManager::class)->submit($question, $user);
        self::assertSame($user, $question->getUser());
    }

    public function testCsvExportWritesOneLinePerEvent(): void
    {
        $event = $this->createEvent($this->createOrganizer());
        $event->setEndedAt(null);
        $dir = self::getContainer()->getParameter('csv_directory');

        $fileName = $this->service(CsvService::class)->createEventCsv([$event]);

        $lines = file($dir.'/'.$fileName, \FILE_IGNORE_NEW_LINES);
        self::assertIsArray($lines);
        self::assertCount(1, $lines);
        self::assertStringStartsWith('"Concert test"', $lines[0]);
        self::assertStringContainsString('Durand', $lines[0]);
    }

    public function testPictureUploadAndDeletion(): void
    {
        $dir = self::getContainer()->getParameter('images_directory');
        $source = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($source, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='));
        $service = $this->service(PictureService::class);

        $picture = $service->handleCreate(new File($source));
        self::assertFileExists($dir.'/'.$picture->getPath());
        self::assertStringEndsWith('.png', $picture->getPath());

        $event = $this->createEvent($this->createOrganizer());
        $event->addPicture($picture->setTitle('Photo'));
        $this->em()->flush();
        $id = $picture->getId();

        $service->handleDelete($picture);
        self::assertFileDoesNotExist($dir.'/'.$picture->getPath());
        self::assertNull($this->em()->find(Picture::class, $id));
    }
}

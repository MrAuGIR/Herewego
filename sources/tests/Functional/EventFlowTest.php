<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Event;
use App\Entity\EventGroup;
use App\Entity\Picture;
use App\Tests\Support\FunctionalTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class EventFlowTest extends FunctionalTestCase
{
    /**
     * @return array<string, mixed>
     */
    private static function eventFormValues(int $categoryId, string $title = 'Festival de printemps'): array
    {
        return [
            'event[title]' => $title,
            'event[description]' => '<p>Programme <script>alert(1)</script>complet</p>',
            'event[startedAt][date]' => (new \DateTime('+1 month'))->format('Y-m-d'),
            'event[startedAt][time]' => '18:00',
            'event[endedAt][date]' => (new \DateTime('+1 month +1 day'))->format('Y-m-d'),
            'event[endedAt][time]' => '23:00',
            'event[category]' => (string) $categoryId,
            'event[localisation][cityName]' => 'Grenoble',
            'event[localisation][cityCp]' => '38000',
            'event[localisation][adress]' => '2 place Verdun',
        ];
    }

    public function testShowCountsViewsAndDetectsParticipation(): void
    {
        $event = $this->createEvent($this->createOrganizer());
        $user = $this->createUser();
        $this->createParticipation($event, $user);
        $this->createPicture($event);

        $this->client->request('GET', '/event/show/'.$event->getId());
        self::assertResponseIsSuccessful();

        $this->loginAs($user);
        $this->client->request('GET', '/event/show/'.$event->getId());
        self::assertResponseIsSuccessful();

        self::assertSame(2, $this->em()->find(Event::class, $event->getId())?->getCountViews());
    }

    public function testParticipateThenCancel(): void
    {
        $event = $this->createEvent($this->createOrganizer());
        $user = $this->createUser();
        $this->loginAs($user);

        $this->client->request('GET', '/event/participate/'.$event->getId());
        self::assertResponseRedirects('/event/show/'.$event->getId());
        self::assertQueuedEmailCount(1);

        // Déjà participant : participer à nouveau est refusé.
        $this->client->request('GET', '/event/participate/'.$event->getId());
        self::assertResponseRedirects('/');

        $this->client->request('GET', '/event/cancel/'.$event->getId());
        self::assertResponseRedirects('/event/show/'.$event->getId());
        $this->client->request('GET', '/event/cancel/'.$event->getId());
        self::assertResponseRedirects('/');
    }

    public function testAnonymousCannotParticipate(): void
    {
        $event = $this->createEvent($this->createOrganizer());

        $this->client->request('GET', '/event/participate/'.$event->getId());

        self::assertResponseRedirects('/login');
    }

    public function testOrganizerCreatesEventWithSanitizedDescription(): void
    {
        $category = $this->createCategory();
        $this->loginAs($this->createOrganizer());

        $this->submitForm('/event/create', 'event', self::eventFormValues((int) $category->getId()));

        $event = $this->em()->getRepository(Event::class)->findOneBy(['title' => 'Festival de printemps']);
        self::assertNotNull($event);
        self::assertResponseRedirects('/event/show/'.$event->getId());
        self::assertStringNotContainsString('<script', (string) $event->getDescription());
        self::assertSame('festival-de-printemps', $event->getSlug());
        self::assertStringContainsString('<div', (string) $event->getTag());
    }

    public function testOrganizerCreatesEventWithPicture(): void
    {
        $category = $this->createCategory();
        $this->loginAs($this->createOrganizer());
        $image = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($image, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='));

        $crawler = $this->client->request('GET', '/event/create');
        $form = $crawler->filter('form[name="event"]')->form(self::eventFormValues((int) $category->getId(), 'Avec photo'));
        $this->client->request($form->getMethod(), $form->getUri(), $form->getPhpValues(), [
            'event' => ['pictures' => [new UploadedFile($image, 'photo.png', 'image/png', null, true)]],
        ]);

        $event = $this->em()->getRepository(Event::class)->findOneBy(['title' => 'Avec photo']);
        self::assertNotNull($event);
        self::assertCount(1, $this->em()->getRepository(Picture::class)->findBy(['event' => $event]));
    }

    public function testInvalidEventIsRejected(): void
    {
        $this->loginAs($this->createOrganizer());

        $this->submitForm('/event/create', 'event', ['event[title]' => 'x']);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.alert-danger, .invalid-feedback, .form-error-message');
    }

    public function testSimpleUserCannotCreateEvent(): void
    {
        $this->loginAs($this->createUser());

        $this->client->request('GET', '/event/create');

        self::assertResponseRedirects('/');
    }

    public function testOwnerEditsAndDeletesEvent(): void
    {
        $organizer = $this->createOrganizer();
        $event = $this->createEvent($organizer);
        $id = $event->getId();
        $this->loginAs($organizer);

        $this->submitForm('/event/edit/'.$id, 'event', self::eventFormValues((int) $event->getCategory()->getId(), 'Titre modifié'));
        self::assertResponseRedirects('/event/show/'.$id);

        $this->submitForm('/event/edit/'.$id, 'event', ['event[title]' => '']);
        self::assertResponseIsSuccessful();

        $this->client->request('DELETE', '/event/delete/'.$id);
        self::assertResponseRedirects('/event/');
        $this->em()->clear();
        self::assertNull($this->em()->find(Event::class, $id));
    }

    public function testOtherOrganizerCannotEdit(): void
    {
        $event = $this->createEvent($this->createOrganizer());
        $this->loginAs($this->createOrganizer('intrus@test.fr'));

        $this->client->request('GET', '/event/edit/'.$event->getId());

        self::assertResponseRedirects('/');
    }

    public function testPictureOrderAndDeletionRequireValidToken(): void
    {
        $organizer = $this->createOrganizer();
        $event = $this->createEvent($organizer);
        $dir = self::getContainer()->getParameter('images_directory');
        @mkdir($dir, 0o777, true);
        touch($dir.'/to-delete.jpg');
        $picture = $this->createPicture($event, 'to-delete.jpg');
        $pictureId = $picture->getId();
        $this->loginAs($organizer);

        $crawler = $this->client->request('GET', '/event/edit/'.$event->getId());
        $orderToken = (string) $crawler->filter('input[data-id="'.$pictureId.'"]')->attr('data-token');
        $deleteToken = (string) $crawler->filter('a[data-delete]')->attr('data-token');

        $this->client->request('POST', '/event/picture/order/'.$pictureId, ['_token' => 'bad', 'value' => 3]);
        self::assertResponseStatusCodeSame(400);
        $this->client->request('POST', '/event/picture/order/'.$pictureId, ['_token' => $orderToken, 'value' => 3]);
        self::assertResponseIsSuccessful();

        $this->client->request('DELETE', '/event/picture/delete/'.$pictureId, content: (string) json_encode(['_token' => 'bad']));
        self::assertResponseStatusCodeSame(400);
        $this->client->request('DELETE', '/event/picture/delete/'.$pictureId, content: (string) json_encode(['_token' => $deleteToken]));
        self::assertResponseIsSuccessful();
        self::assertFileDoesNotExist($dir.'/to-delete.jpg');
    }

    public function testGroupPage(): void
    {
        $group = (new EventGroup())->setName('Saison 2026');
        $this->em()->persist($group);
        $event = $this->createEvent($this->createOrganizer());
        $event->setEventGroup($group);
        $this->em()->flush();

        $this->client->request('GET', '/event/group/'.$group->getId());

        self::assertResponseIsSuccessful();
    }
}

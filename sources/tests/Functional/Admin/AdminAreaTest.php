<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\QuestionAdmin;
use App\Entity\QuestionUser;
use App\Entity\User;
use App\Service\Organizer\Siret\ApiCheckSiret;
use App\Tests\Support\FunctionalTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class AdminAreaTest extends FunctionalTestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function adminPages(): iterable
    {
        yield 'dashboard' => ['/admin/'];
        yield 'categories' => ['/admin/category/'];
        yield 'category create' => ['/admin/category/create'];
        yield 'events' => ['/admin/event/'];
        yield 'event create' => ['/admin/event/create'];
        yield 'faq users' => ['/admin/faq/'];
        yield 'faq admin' => ['/admin/faq/liste'];
        yield 'faq create' => ['/admin/faq/create/qAdmin'];
        yield 'organizers' => ['/admin/organizer/'];
        yield 'organizer create' => ['/admin/organizer/create'];
        yield 'users' => ['/admin/user/'];
        yield 'user create' => ['/admin/user/create'];
    }

    #[DataProvider('adminPages')]
    public function testAdminPagesRender(string $uri): void
    {
        $this->createEvent($this->createOrganizer());
        $this->createUser();
        $this->createQuestionUser();
        $this->createQuestionAdmin();
        $this->loginAs($this->createAdmin());

        $this->client->request('GET', $uri);

        self::assertResponseIsSuccessful($uri);
    }

    #[DataProvider('adminPages')]
    public function testAdminPagesAreHiddenFromOthers(string $uri): void
    {
        $this->loginAs($this->createOrganizer());

        $this->client->request('GET', $uri);

        // access_control ^/admin -> AccessDeniedHandler : retour à l'accueil.
        self::assertResponseRedirects('/');
    }

    public function testCategoryCrud(): void
    {
        $this->loginAs($this->createAdmin());
        $logo = tempnam(sys_get_temp_dir(), 'logo');
        file_put_contents($logo, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='));

        $crawler = $this->client->request('GET', '/admin/category/create');
        $form = $crawler->filter('form[name="category"]')->form(['category[name]' => 'Musique Live', 'category[color]' => '#ff0000']);
        $this->client->request('POST', $form->getUri(), $form->getPhpValues(), ['category' => ['pathLogo' => new UploadedFile($logo, 'logo.png', 'image/png', null, true)]]);
        self::assertResponseRedirects('/admin/category/');

        $category = $this->em()->getRepository(Category::class)->findOneBy(['name' => 'Musique Live']);
        self::assertNotNull($category);
        self::assertSame('musique-live', $category->getSlug());
        self::assertNotEmpty($category->getPathLogo());

        $this->submitForm('/admin/category/edit/'.$category->getId(), 'category', ['category[name]' => 'Concerts']);
        self::assertResponseRedirects('/admin/category/');

        $token = $this->csrfFrom('/admin/category/', '/admin/category/delete/');
        $this->client->request('POST', '/admin/category/delete/'.$category->getId(), ['_token' => 'bad']);
        self::assertResponseRedirects('/');
        $this->client->request('POST', '/admin/category/delete/'.$category->getId(), ['_token' => $token]);
        self::assertResponseRedirects('/admin/category/');
    }

    public function testFaqCrud(): void
    {
        $this->loginAs($this->createAdmin());
        $values = [
            'question_admin[question]' => '<p>Puis-je <script>x</script>venir ?</p>',
            'question_admin[answer]' => '<p>Oui</p>',
            'question_admin[importance]' => '3',
        ];

        $this->submitForm('/admin/faq/create/qAdmin', 'question_admin', $values);
        self::assertResponseRedirects('/admin/faq/liste');
        $question = $this->em()->getRepository(QuestionAdmin::class)->findOneBy(['importance' => 3]);
        self::assertNotNull($question);
        self::assertStringNotContainsString('<script', $question->getQuestion());

        $this->submitForm('/admin/faq/edit/qAdmin/'.$question->getId(), 'question_admin', ['question_admin[importance]' => '5'] + $values);
        self::assertResponseRedirects('/admin/faq/liste');

        $this->client->request('GET', '/admin/faq/show/qAdmin/'.$question->getId());
        self::assertResponseRedirects('/admin/faq/liste');
        $this->client->request('DELETE', '/admin/faq/delete/qAdmin/'.$question->getId());
        self::assertResponseRedirects('/admin/faq/liste');

        $questionUser = $this->createQuestionUser($this->createUser());
        $this->client->request('GET', '/admin/faq/show/qUser/'.$questionUser->getId());
        self::assertResponseRedirects('/admin/faq/');
        $this->client->request('DELETE', '/admin/faq/delete/qUser/'.$questionUser->getId());
        self::assertResponseRedirects('/admin/faq/');
        self::assertSame(0, $this->em()->getRepository(QuestionUser::class)->count([]));
    }

    public function testEventCrud(): void
    {
        $admin = $this->createAdmin();
        $event = $this->createEvent($this->createOrganizer());
        $this->createTransport($event, $this->createUser());
        $this->loginAs($admin);

        $this->client->request('GET', '/admin/event/show/'.$event->getId());
        self::assertResponseIsSuccessful();

        $values = [
            'event[title]' => 'Créé par admin',
            'event[description]' => '<p>Desc</p>',
            'event[startedAt][date]' => (new \DateTime('+1 month'))->format('Y-m-d'),
            'event[startedAt][time]' => '10:00',
            'event[endedAt][date]' => (new \DateTime('+1 month'))->format('Y-m-d'),
            'event[endedAt][time]' => '12:00',
            'event[category]' => (string) $event->getCategory()->getId(),
            'event[localisation][cityName]' => 'Nice',
            'event[localisation][cityCp]' => '06000',
            'event[localisation][adress]' => 'Promenade',
        ];
        $this->submitForm('/admin/event/create', 'event', $values);
        self::assertResponseRedirects('/admin/event/');
        self::assertNotNull($this->em()->getRepository(Event::class)->findOneBy(['title' => 'Créé par admin']));

        $this->submitForm('/admin/event/edit/'.$event->getId(), 'event', ['event[title]' => 'Edité par admin']);
        self::assertResponseRedirects('/admin/event/');
        $this->submitForm('/admin/event/edit/'.$event->getId(), 'event', ['event[title]' => '']);
        self::assertResponseIsSuccessful();

        $id = $event->getId();
        $this->client->request('DELETE', '/admin/event/delete/'.$id);
        self::assertResponseRedirects('/admin/event/');
        $this->em()->clear();
        self::assertNull($this->em()->find(Event::class, $id));
    }

    public function testUserAndOrganizerManagement(): void
    {
        $this->loginAs($this->createAdmin());
        $pending = $this->createUser('pending@test.fr', validated: false);
        $organizer = $this->createOrganizer();

        // Bascule de validation (liste utilisateurs, appel ajax).
        $this->client->request('GET', '/admin/user/?ajax=1&userId='.$pending->getId());
        self::assertResponseIsSuccessful();
        $this->client->request('GET', '/admin/organizer/?ajax=1&userId='.$organizer->getId());
        self::assertResponseIsSuccessful();
        $this->em()->clear();
        self::assertTrue($this->em()->find(User::class, $pending->getId())?->getIsValidate());
        self::assertFalse($this->em()->find(User::class, $organizer->getId())?->getIsValidate());

        foreach (['/admin/user/edit/'.$pending->getId(), '/admin/organizer/edit/'.$organizer->getId()] as $uri) {
            $this->client->request('GET', $uri);
            self::assertResponseIsSuccessful($uri);
        }

        $register = [
            'register[password]' => 'Secret123',
            'register[confirmPassword]' => 'Secret123',
            'register[lastname]' => 'Admin',
            'register[firstname]' => 'Créé',
            'register[localisation][cityName]' => 'Lyon',
            'register[localisation][cityCp]' => '69000',
            'register[localisation][adress]' => 'rue',
        ];
        $this->submitForm('/admin/user/create', 'register', ['register[email]' => 'u2@test.fr'] + $register);
        self::assertResponseRedirects('/admin/user/');
        $this->submitForm('/admin/organizer/create', 'register', ['register[email]' => 'o2@test.fr'] + $register);
        self::assertResponseRedirects('/admin/organizer/');

        $this->submitForm('/admin/user/edit/'.$pending->getId(), 'register', ['register[email]' => 'renamed@test.fr'] + $register);
        self::assertResponseRedirects('/admin/user/');
        $this->submitForm('/admin/organizer/edit/'.$organizer->getId(), 'register', ['register[email]' => 'orga2@test.fr'] + $register);
        self::assertResponseRedirects('/admin/organizer/');

        $this->client->request('POST', '/admin/user/delete/'.$pending->getId(), ['_token' => 'bad']);
        self::assertResponseRedirects('/');
        $token = $this->csrfFrom('/admin/user/', '/admin/user/delete/'.$pending->getId());
        $this->client->request('POST', '/admin/user/delete/'.$pending->getId(), ['_token' => $token]);
        self::assertResponseRedirects('/admin/user/');

        $this->client->request('POST', '/admin/organizer/delete/'.$organizer->getId(), ['_token' => 'bad']);
        self::assertResponseRedirects('/');
        $token = $this->csrfFrom('/admin/organizer/', '/admin/organizer/delete/'.$organizer->getId());
        $this->client->request('POST', '/admin/organizer/delete/'.$organizer->getId(), ['_token' => $token]);
        self::assertResponseRedirects('/admin/organizer/');
    }

    public function testSiretVerification(): void
    {
        $this->loginAs($this->createAdmin());
        $organizer = $this->createOrganizer();
        $noSiret = $this->createOrganizer('nosiret@test.fr')->setSiret(null);
        $this->em()->flush();

        $this->client->disableReboot();
        self::getContainer()->set(ApiCheckSiret::class, new ApiCheckSiret(new MockHttpClient([
            new MockResponse('{}', ['http_code' => 200]),
            new MockResponse('', ['http_code' => 404]),
        ])));

        foreach (['success', 'danger'] as $flash) {
            $this->client->request('GET', '/admin/organizer/verifySiret/'.$organizer->getId());
            self::assertResponseRedirects('/admin/organizer/edit/'.$organizer->getId());
            $this->client->followRedirect();
            self::assertSelectorExists('.alert-'.$flash);
        }

        $this->client->request('GET', '/admin/organizer/verifySiret/'.$noSiret->getId());
        self::assertResponseRedirects('/admin/organizer/edit/'.$noSiret->getId());
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Support\FunctionalTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Pages publiques en 200, avec et sans données.
 */
final class SmokeTest extends FunctionalTestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function publicPages(): iterable
    {
        yield 'home' => ['/'];
        yield 'faq' => ['/faq/'];
        yield 'events list' => ['/event/'];
        yield 'login' => ['/login'];
        yield 'register' => ['/register'];
        yield 'register user' => ['/register/user'];
        yield 'register organizer' => ['/register/organizer'];
        yield 'cgu' => ['/cgu'];
        yield 'legal notice' => ['/legal-Notice'];
        yield 'plan' => ['/plan'];
        yield 'privacy' => ['/privacy-Policy'];
        yield 'who are we' => ['/who-are-we'];
    }

    #[DataProvider('publicPages')]
    public function testPublicPagesRespondOk(string $path): void
    {
        $this->client->request('GET', $path);

        self::assertResponseIsSuccessful(\sprintf('La page %s doit répondre 200', $path));
    }

    public function testPagesWithContent(): void
    {
        $event = $this->createEvent($this->createOrganizer());
        $this->createQuestionAdmin();

        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Concert test', $crawler->text());

        $this->client->request('GET', '/event/?categories[]='.$event->getCategory()->getId().'&localisation=Lyon&q=Concert');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Concert test');

        $this->client->request('GET', '/faq/');
        self::assertSelectorTextContains('body', 'Comment ça marche ?');

        $this->client->request('GET', '/event/category/'.$event->getCategory()->getId());
        self::assertResponseIsSuccessful();
    }

    public function testAjaxListingsReturnJson(): void
    {
        $this->createEvent($this->createOrganizer());

        foreach (['/?ajax=1', '/event/?ajax=1&page=1&order=DESC'] as $uri) {
            $this->client->request('GET', $uri);
            self::assertResponseIsSuccessful();
            $data = json_decode((string) $this->client->getResponse()->getContent(), true);
            self::assertIsArray($data);
            self::assertStringContainsString('Concert test', $data['content']);
        }
    }

    public function testUnknownCategoryRedirectsHomeWithFlash(): void
    {
        $this->client->request('GET', '/event/category/999999');

        self::assertResponseRedirects('/');
    }

    public function testLocaleSwitchIsStoredInSession(): void
    {
        $this->client->request('GET', '/local/en');
        self::assertResponseRedirects('/');

        $this->client->followRedirect();
        self::assertSame('en', $this->client->getRequest()->getLocale());
    }
}

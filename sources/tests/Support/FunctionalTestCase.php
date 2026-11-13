<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Base des tests HTTP : client créé avant toute création d'entité (le kernel
 * ne doit pas être booté avant createClient()).
 */
abstract class FunctionalTestCase extends WebTestCase
{
    use CreatesEntities;

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    protected function loginAs(User $user): void
    {
        $this->client->loginUser($user);
    }

    /**
     * Soumet le formulaire Symfony nommé $formName présent sur $uri.
     *
     * @param array<string, mixed> $values
     */
    protected function submitForm(string $uri, string $formName, array $values = [], ?string $method = null): Crawler
    {
        $crawler = $this->client->request('GET', $uri);
        self::assertResponseIsSuccessful();
        $form = $crawler->filter(\sprintf('form[name="%s"]', $formName))->form($values, $method);

        return $this->client->submit($form);
    }

    /**
     * Récupère la valeur du token CSRF d'un formulaire de la page $uri dont l'action contient $action.
     */
    protected function csrfFrom(string $uri, string $action): string
    {
        $crawler = $this->client->request('GET', $uri);
        self::assertResponseIsSuccessful();

        return (string) $crawler->filter(\sprintf('form[action*="%s"] input[name="_token"]', $action))->attr('value');
    }
}

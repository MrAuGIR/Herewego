<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pages publiques en 200 après refonte (valide notamment les requêtes
 * EventRepository corrigées, appelées par / et /event/).
 */
final class SmokeTest extends WebTestCase
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
    }

    #[DataProvider('publicPages')]
    public function testPublicPagesRespondOk(string $path): void
    {
        $client = self::createClient();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($em);
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);

        $client->request('GET', $path);

        self::assertSame(
            Response::HTTP_OK,
            $client->getResponse()->getStatusCode(),
            sprintf('La page %s doit répondre 200', $path),
        );
    }
}

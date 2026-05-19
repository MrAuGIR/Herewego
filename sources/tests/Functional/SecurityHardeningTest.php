<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vérifie le durcissement Lot 4 : plus de GET destructif, accès admin protégé.
 */
final class SecurityHardeningTest extends WebTestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function destructiveGetRoutes(): iterable
    {
        yield 'accept ticket (POST only)' => ['/transport/manage/accept/1'];
        yield 'decline ticket (POST only)' => ['/transport/manage/decline/1'];
        yield 'transport delete (POST/DELETE only)' => ['/transport/delete/1'];
        yield 'picture order (POST only)' => ['/event/picture/order/1'];
        yield 'account delete (POST/DELETE only)' => ['/user/profile/delete'];
    }

    #[DataProvider('destructiveGetRoutes')]
    public function testDestructiveActionsRejectGet(string $path): void
    {
        $client = self::createClient();
        $client->request('GET', $path);

        self::assertSame(
            Response::HTTP_METHOD_NOT_ALLOWED,
            $client->getResponse()->getStatusCode(),
            sprintf('%s ne doit plus être accessible en GET', $path),
        );
    }

    public function testAdminAreaBlocksAnonymous(): void
    {
        $client = self::createClient();
        $client->request('GET', '/admin');

        $status = $client->getResponse()->getStatusCode();
        // access_control ^/admin → redirection login (302) ou 401/403.
        self::assertContains($status, [
            Response::HTTP_FOUND,
            Response::HTTP_UNAUTHORIZED,
            Response::HTTP_FORBIDDEN,
        ]);
    }

    public function testUserAreaRequiresAuthentication(): void
    {
        $client = self::createClient();
        $client->request('GET', '/user/profile');

        self::assertNotSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }
}

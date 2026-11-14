<?php

declare(strict_types=1);

namespace App\Tests\Integration\Cache;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Non-régression : le cache applicatif (et le pool du rate limiter qui en hérite)
 * est stocké en base, jamais en fichiers. Le stockage fichier peut saturer le
 * quota d'inodes de l'hébergement (un fichier par entrée, jamais supprimé).
 */
final class CacheStorageTest extends KernelTestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function poolProvider(): iterable
    {
        yield 'cache.app' => ['cache.app'];
        yield 'cache.rate_limiter' => ['cache.rate_limiter'];
    }

    #[DataProvider('poolProvider')]
    public function testPoolIsStoredInDatabase(string $poolId): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $pool = $container->get($poolId);
        self::assertInstanceOf(CacheItemPoolInterface::class, $pool);
        $connection = $container->get(Connection::class);
        $filesBefore = $this->countPoolFiles();
        $rowsBefore = (int) $connection->fetchOne('SELECT COUNT(*) FROM cache_items');

        $pool->save($pool->getItem('probe_'.bin2hex(random_bytes(4)))->set('value'));

        self::assertSame($rowsBefore + 1, (int) $connection->fetchOne('SELECT COUNT(*) FROM cache_items'));
        self::assertSame($filesBefore, $this->countPoolFiles(), 'aucun fichier ne doit être écrit dans var/cache/*/pools/app');
    }

    public function testExpiredEntriesArePruned(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $pool = $container->get('cache.app');
        $connection = $container->get(Connection::class);

        $pool->save($pool->getItem('expired')->set('x')->expiresAfter(3600));
        $pool->save($pool->getItem('alive')->set('y')->expiresAfter(3600));
        $connection->executeStatement('UPDATE cache_items SET item_lifetime = 1, item_time = item_time - 7200 WHERE item_id LIKE ?', ['%expired']);

        self::assertTrue($pool->prune());

        self::assertSame(0, (int) $connection->fetchOne('SELECT COUNT(*) FROM cache_items WHERE item_id LIKE ?', ['%expired']));
        self::assertSame(1, (int) $connection->fetchOne('SELECT COUNT(*) FROM cache_items WHERE item_id LIKE ?', ['%alive']));
    }

    private function countPoolFiles(): int
    {
        $dir = static::getContainer()->getParameter('kernel.cache_dir').'/pools/app';
        if (!is_dir($dir)) {
            return 0;
        }

        return iterator_count(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)));
    }
}

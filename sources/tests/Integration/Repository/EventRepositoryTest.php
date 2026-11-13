<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Dto\EventQueryDto;
use App\Entity\Category;
use App\Repository\EventRepository;
use App\Tests\Support\CreatesEntities;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Vérifie que les requêtes corrigées (Lot 3) s'exécutent sans erreur DQL.
 *
 * Avant correction : EventRepository::findLast($category) générait un DQL
 * invalide, et getCountEvent un paramètre ':cats' incohérent.
 */
final class EventRepositoryTest extends KernelTestCase
{
    use CreatesEntities;

    private function repo(): EventRepository
    {
        return self::getContainer()->get(EventRepository::class);
    }

    public function testFindLastWithoutCategory(): void
    {
        self::assertSame([], $this->repo()->findLast());
    }

    public function testFindLastWithCategoryExecutes(): void
    {
        $category = (new Category())->setName('Sport')->setSlug('sport')->setColor('#000');
        $this->em()->persist($category);
        $this->em()->flush();

        // Ne doit pas lever d'exception DQL (bug corrigé).
        self::assertSame([], $this->repo()->findLast($category));
    }

    public function testFindByFiltersAndCountWithCategories(): void
    {
        $dto = new EventQueryDto(limit: 12, page: 1, order: 'ASC', categories: [1, 2]);

        self::assertSame([], $this->repo()->findByFilters($dto));
        self::assertSame(0, $this->repo()->getCountEvent($dto));
    }

    public function testPaginationIsClampedForHostileInput(): void
    {
        // page/limit hostiles : ne doivent pas produire d'offset négatif / d'erreur.
        $dto = new EventQueryDto(limit: 99999, page: 1, order: 'ASC');

        self::assertSame([], $this->repo()->findByFilters($dto));
    }
}

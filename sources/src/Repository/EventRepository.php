<?php

namespace App\Repository;

use App\Dto\EventQueryDto;
use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * Retourne les derniers évènements créés (général ou par catégorie).
     *
     * @return Event[]
     */
    public function findLast($category = null): array
    {
        if (null === $category) {
            return $this->createQueryBuilder('e')
                ->orderBy('e.createdAt', 'ASC')
                ->setMaxResults(6)
                ->getQuery()
                ->getResult();
        }

        return $this->createQueryBuilder('e')
            ->andWhere('e.category', $category)
            ->orderBy('e.createdAt', 'ASC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les events les plus populaires.
     *
     * @return Event[]
     */
    public function findByPopularity(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.countViews', 'ASC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();
    }

    public function findByFilters(EventQueryDto $dto): array
    {
        $date = new \DateTime();

        $query = $this->createQueryBuilder('e')
            ->andWhere('e.endedAt > :date')
            ->setParameter('date', $date);


        if (! empty($categories = $dto->categories)) {
            $query->andWhere('e.category in (:cats)')
                ->setParameter('cats', array_values($categories));
        }

        if (! empty($keyWord = $dto->q)) {
            $query->andWhere('e.title LIKE :title')
            ->setParameter('title', $keyWord.'%');
        }

        if (! empty($localisation = $dto->localisation)) {
            $query->innerJoin('e.localisation', 'l')
                ->andWhere('l.cityName LIKE :localisation')
                ->setParameter('localisation', $localisation.'%');
        }

        if (! empty($order = $dto->order)) {
            $query->orderBy('e.startedAt', $order)
                ->setFirstResult(($dto->page * $dto->limit) - $dto->limit)
                ->setMaxResults($dto->limit);
        } else {
            $query->orderBy('e.startedAt', 'ASC')
                ->setFirstResult(($dto->page * $dto->limit) - $dto->limit)
                ->setMaxResults($dto->limit);
        }

        return $query->getQuery()->getResult();
    }

    public function getCountEvent(EventQueryDto $dto)
    {
        $date = new \DateTime();

        $query = $this->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->where('c.endedAt > :date')
            ->setParameter('date', $date);

        if (! empty($categories = $dto->categories)) {
            $query->andWhere('c.category IN(:cats)')
                ->setParameter(':cats', array_values($categories));
        }

        if (! empty($localisation = $dto->localisation)) {
            $query->innerJoin('c.localisation', 'l')
                ->andWhere('l.cityName LIKE :localisation')
                ->setParameter('localisation', $localisation.'%');
        }

        // return the scalar result
        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @return Event[]
     */
    public function findByDateAfterNow($organizerId): array
    {
        $date = new \DateTime();

        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :organizerId')
            ->andWhere('e.endedAt > :date')
            ->setParameter('date', $date)
            ->setParameter('organizerId', $organizerId)
            // ->orderBy('e.startedAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Event[]
     */
    public function findByDateBeforeNow($organizerId): array
    {
        $date = new \DateTime();

        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :organizerId')
            ->andWhere('e.endedAt < :date')
            ->setParameter('date', $date)
            ->setParameter('organizerId', $organizerId)
            // ->orderBy('e.startedAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}

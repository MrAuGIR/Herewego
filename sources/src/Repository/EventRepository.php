<?php

namespace App\Repository;

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

    // /**
    //  * @return Event[] Returns an array of Event objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
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

    /**
     * Retourne les events en fonction des filtres de recherche.
     *
     * @param $page  numero de la page en cours
     * @param $limit nombre d'event a afficher par page
     *
     * @return Event[]
     */
    public function findByFilters($page, $limit, $order, $filtersCat = null, $localisation = null, $keyWord = null): array
    {
        // date actuelle
        $date = new \DateTime();

        // les events qui ne sont pas encore passé
        $query = $this->createQueryBuilder('e')
            ->andWhere('e.endedAt > :date')
            ->setParameter('date', $date);


        // si les filtres ne sont pas null
        if (null != $filtersCat) {
            $query->andWhere('e.category in (:cats)')
                ->setParameter('cats', array_values($filtersCat));
        }

        // si le mot clé n'est pas null
        if (null != $keyWord) {
            $query->andWhere('e.title LIKE :title')
            ->setParameter('title', $keyWord.'%');
        }

        // si la localisation n'est pas null
        if (null != $localisation) {
            $query->innerJoin('e.localisation', 'l')
                ->andWhere('l.cityName LIKE :localisation')
                ->setParameter('localisation', $localisation.'%');
        }

        // ordre d'affichage et gestion des resultat en fonction de la page en cours
        if (null != $order) {
            $query->orderBy('e.startedAt', $order)
                ->setFirstResult(($page * $limit) - $limit)
                ->setMaxResults($limit);
        } else {
            $query->orderBy('e.startedAt', 'ASC')
                ->setFirstResult(($page * $limit) - $limit)
                ->setMaxResults($limit);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Compte le nombre d'event retourné en fonction des filtres.
     */
    public function getCountEvent($filtersCat = null, $localisation = null)
    {
        $date = new \DateTime();

        $query = $this->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->where('c.endedAt > :date')
            ->setParameter('date', $date);

        if (null != $filtersCat) {
            $query->andWhere('c.category IN(:cats)')
                ->setParameter(':cats', array_values($filtersCat));
        }

        if (null != $localisation) {
            $query->innerJoin('c.localisation', 'l')
                ->andWhere('l.cityName LIKE :localisation')
                ->setParameter('localisation', $localisation.'%');
        }

        // return the scalar result
        return $query->getQuery()->getSingleScalarResult();
    }

    /*
    public function findOneBySomeField($value): ?Event
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findByDateAfterNow($organizerId)
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

    public function findByDateBeforeNow($organizerId)
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

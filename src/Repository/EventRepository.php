<?php

namespace App\Repository;

use DateTime;
use App\Entity\Event;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
     * Retourne les derniers évènements créés (général ou par catégorie)
     * @return Event[]
     */
    public function findLast($category = null){

        if($category === null){
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
     * Retourne les events les plus populaires
     * @return Event[]
     */
    public function findByPopularity(){

        return $this->createQueryBuilder('e')
            ->orderBy('e.countViews', 'ASC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();
        
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
        $date = new DateTime;
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
        $date = new DateTime;
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

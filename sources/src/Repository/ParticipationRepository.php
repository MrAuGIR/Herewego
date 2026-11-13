<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Participation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participation>
 */
class ParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participation::class);
    }

    // /**
    //  * @return Participation[] Returns an array of Participation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /**
     * @return Participation[]
     */
    public function findByDateBeforeNow(?int $userId): array
    {
        $date = new \DateTime();

        return $this->createQueryBuilder('p')
            ->innerJoin('p.event', 'e')
            ->where('p.user = :userId')
            ->andWhere('e.endedAt < :date')
            ->setParameter('date', $date)
            ->setParameter('userId', $userId)
            // ->orderBy('e.startedAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Participation[]
     */
    public function findByDateAfterNow(?int $userId): array
    {
        $date = new \DateTime();

        return $this->createQueryBuilder('p')
            ->innerJoin('p.event', 'e')
            ->where('p.user = :userId')
            ->andWhere('e.endedAt > :date')
            ->setParameter('date', $date)
            ->setParameter('userId', $userId)
            ->orderBy('e.startedAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    // $qb->select('c')
    // ->innerJoin('c.phones', 'p', 'WITH', 'p.phone = :phone')
    // ->where('c.username = :username');

    /*
    public function findOneBySomeField($value): ?Participation
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

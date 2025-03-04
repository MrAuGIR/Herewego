<?php

namespace App\Repository;

use DateTime;
use App\Entity\Participation;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Participation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participation[]    findAll()
 * @method Participation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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

    public function findByDateBeforeNow($userId)
    {
        $date = new DateTime;
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

    public function findByDateAfterNow($userId)
    {
        $date = new DateTime;
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

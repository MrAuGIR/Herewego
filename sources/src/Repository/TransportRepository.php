<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Transport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transport>
 */
class TransportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transport::class);
    }

    // /**
    //  * @return Transport[] Returns an array of Transport objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Transport
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * findTransportToAlert
     * Renvoie les transport débutant dans moins de 2 jours.
     *
     * @return Transport[]
     */
    public function findTransportToAlert(): array
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.goStartedAt BETWEEN :now AND :limit')
            ->setParameter('now', new \DateTime('now'))
            ->setParameter('limit', new \DateTime('+2 days'));

        return $query->getQuery()->getResult();
    }
}

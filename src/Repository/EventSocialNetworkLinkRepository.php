<?php

namespace App\Repository;

use App\Entity\EventSocialNetworkLink;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EventSocialNetworkLink|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventSocialNetworkLink|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventSocialNetworkLink[]    findAll()
 * @method EventSocialNetworkLink[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventSocialNetworkLinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventSocialNetworkLink::class);
    }

    // /**
    //  * @return EventSocialNetworkLink[] Returns an array of EventSocialNetworkLink objects
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

    /*
    public function findOneBySomeField($value): ?EventSocialNetworkLink
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

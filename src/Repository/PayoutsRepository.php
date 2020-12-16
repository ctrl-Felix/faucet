<?php

namespace App\Repository;

use App\Entity\Payouts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Payouts|null find($id, $lockMode = null, $lockVersion = null)
 * @method Payouts|null findOneBy(array $criteria, array $orderBy = null)
 * @method Payouts[]    findAll()
 * @method Payouts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PayoutsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payouts::class);
    }

    // /**
    //  * @return Payouts[] Returns an array of Payouts objects
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

    /*
    public function findOneBySomeField($value): ?Payouts
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

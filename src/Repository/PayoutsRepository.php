<?php

namespace App\Repository;

use App\Entity\Payouts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @method Payouts|null find($id, $lockMode = null, $lockVersion = null)
 * @method Payouts|null findOneBy(array $criteria, array $orderBy = null)
 * @method Payouts[]    findAll()
 * @method Payouts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PayoutsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, ContainerInterface $container)
    {
        $this->container = $container;
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

    public function checkAddressAndIp($address, $ip)
    {
        return $this->createQueryBuilder('p')
            ->Where('p.Time > :claimTime')
            ->andWhere('p.address = :address OR p.ip = :ip')
            ->setParameter('claimTime', date('Y-m-d H:i:s', time () - $this->container->getParameter('claim_difference')))
            ->setParameter('address', $address)
            ->setParameter('ip', $ip)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function countPayouts(){
        return $this->createQueryBuilder('p')
            ->select('Count(p.id)')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function sumPayouts(){
        return $this->createQueryBuilder('p')
            ->select('SUM(p.amount)')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function stagedPayouts(){
        return $this->createQueryBuilder('p')
            ->select('Count(p.id)')
            ->andWhere('p.tx is NULL')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getOpenPayouts(){
        return $this->createQueryBuilder('p')
            ->andWhere('p.tx is NULL')
            ->getQuery()
            ->getResult();
    }

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

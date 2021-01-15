<?php

namespace App\Repository;

use App\Entity\CarRental;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CarRental|null find($id, $lockMode = null, $lockVersion = null)
 * @method CarRental|null findOneBy(array $criteria, array $orderBy = null)
 * @method CarRental[]    findAll()
 * @method CarRental[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarRentalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CarRental::class);
    }

    // /**
    //  * @return CarRental[] Returns an array of CarRental objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CarRental
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

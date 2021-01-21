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

    public function findOneById($id) {
        return $this->createQueryBuilder('c')
            ->where('c.id =:parameter')
            ->setParameter('parameter', $id)
            ->join('c.owner', 'o')
            ->addSelect('o')
            ->getQuery()
            ->getArrayResult();
    }
}

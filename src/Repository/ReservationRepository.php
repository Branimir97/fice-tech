<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findAllAsArray() {
        return $this->createQueryBuilder('r')
            ->join('r.user', 'u')
            ->join('r.vehicle', 'v')
            ->join('v.carRental', 'cr')
            ->join('cr.owner', 'o')
            ->addSelect('u', 'v', 'cr', 'o')
            ->getQuery()
            ->getArrayResult();
    }

    public function findByUserId($id) {
        return $this->createQueryBuilder('r')
            ->where('r.user = :user_id')
            ->setParameter('user_id', $id)
            ->join('r.user', 'u')
            ->join('r.vehicle', 'v')
            ->join('v.carRental', 'cr')
            ->join('cr.owner', 'o')
            ->addSelect('u', 'v', 'cr', 'o')
            ->getQuery()
            ->getArrayResult();
    }

    public function checkOwnerById($user_id) {
        return $this->createQueryBuilder('r')
            ->join('r.vehicle', 'v')
            ->join('v.carRental', 'cr')
            ->where('cr.owner = :user_id')
            ->setParameter('user_id', $user_id)
            ->getQuery()->getArrayResult();
    }
}

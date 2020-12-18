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
        $query = $this->createQueryBuilder('r');
        return $query->getQuery()->getArrayResult();
    }

    public function findAllApproved() {
        $query = $this->createQueryBuilder('r')
            ->where('r.isApproved = :status')
            ->setParameter('status', true);
        return $query->getQuery()->getArrayResult();
    }

    public function findAllNotApproved() {
        $query = $this->createQueryBuilder('r')
            ->where('r.isApproved = :status')
            ->setParameter('status', false);
        return $query->getQuery()->getArrayResult();
    }
}

<?php

namespace App\Repository;

use App\Entity\Vehicle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Vehicle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vehicle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vehicle[]    findAll()
 * @method Vehicle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehicleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }

    public function findAllAsArray() {
        return $this->createQueryBuilder('v')
            ->where('v.status = :status')
            ->setParameter('status', 'Available')
            ->join('v.images', 'i')
            ->addSelect('i')
            ->getQuery()
            ->getArrayResult();
    }

    public function filterAvailableVehiclesByLocationAndDates($start, $end) {
        $startDate = new \DateTime($start);
        $endDate = new \DateTime($end);
        return $this->createQueryBuilder('v')
            ->where('v.status != :status')
            ->setParameter('status', "Reserved")
            ->join('v.reservations', 'r')
            ->Andwhere('r.startTime NOT BETWEEN :start AND :end')
            ->Andwhere('r.endTime NOT BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getArrayResult();
    }

    public function filterAvailableVehiclesByCarRental($id) {
        return $this->createQueryBuilder('v')
            ->where('v.status != :status')
            ->setParameter('status', "Reserved")
            ->andWhere('v.carRental = :car_rental_id')
            ->setParameter('car_rental_id', $id)
            ->getQuery()
            ->getArrayResult();
    }
}

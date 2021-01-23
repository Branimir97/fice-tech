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
            ->join('v.images', 'i')
            ->addSelect('i')
            ->join('v.carRental', 'r')
            ->addSelect('r')
            ->getQuery()
            ->getArrayResult();
    }

    public function filterAvailableByStatusAndLocation($location) {
        return $this->createQueryBuilder('v')
            ->where('v.status = :status')
            ->setParameter('status', 'Available')
            ->join('v.carRental', 'cr')
            ->andWhere('cr.city = :city')
            ->setParameter('city', $location)
            ->addSelect('cr')
            ->join('v.images', 'i')
            ->addSelect('i')
            ->getQuery()
            ->getArrayResult();
    }

    public function filterReservedByDatesAndLocation($location, $startTime, $endTime) {
        $startDate = new \DateTime($startTime);
        $endDate = new \DateTime($endTime);
        return $this->createQueryBuilder('v')
            ->where('v.status = :status')
            ->setParameter('status', 'Reserved')
            ->join('v.carRental', 'cr')
            ->andWhere('cr.city = :location')
            ->setParameter('location', $location)
            ->join('v.reservations', 'r')
            ->andWhere(':startDate NOT BETWEEN r.startTime AND r.endTime')
            ->andWhere(':endDate NOT BETWEEN r.startTime AND r.endTime')
            ->andWhere('r.startTime NOT BETWEEN :startDate AND :endDate')
            ->andWhere('r.endTime NOT BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->andWhere('r.status = :reservation_status')
            ->setParameter('reservation_status', 'accepted')
            ->addSelect('cr')
            ->join('v.images', 'i')
            ->addSelect('i')
            ->getQuery()
            ->getArrayResult();
    }

    public function filterAvailableVehiclesByCarRental($id) {
        return $this->createQueryBuilder('v')
            ->where('v.status != :status')
            ->setParameter('status', "Reserved")
            ->andWhere('v.carRental = :car_rental_id')
            ->setParameter('car_rental_id', $id)
            ->join('v.carRental', 'cr')
            ->addSelect('cr')
            ->join('v.images', 'i')
            ->addSelect('i')
            ->getQuery()
            ->getArrayResult();
    }
}

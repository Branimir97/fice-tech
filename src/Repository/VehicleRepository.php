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
            ->join('v.carRental', 'r')
            ->addSelect('r')
            ->getQuery()
            ->getArrayResult();
    }

    public function filterAvailableVehiclesByLocationAndDates($location, $start, $end) {
        $startDate = new \DateTime($start);
        $endDate = new \DateTime($end);
        return $this->createQueryBuilder('v')
            ->where('v.status != :status')
            ->setParameter('status', "Reserved")
            ->join('v.reservations', 'r')
            ->Andwhere(':start NOT BETWEEN r.startTime AND r.endTime')
            ->Andwhere(':end NOT BETWEEN r.startTime AND r.endTime')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->join('v.carRental', 'c')
            ->Andwhere('c.city = :city')
            ->setParameter('city', $location)
            ->join('v.carRental', 'cr')
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

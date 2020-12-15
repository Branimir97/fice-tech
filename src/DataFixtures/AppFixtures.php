<?php

namespace App\DataFixtures;

use App\Entity\Vehicle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        $vehicle = new Vehicle();
        $vehicle->setMark('Marka#1');
        $vehicle->setModel('Model#1');
        $vehicle->setModelYear(new \DateTime('now'));
        $vehicle->setManufactureYear(new \DateTime('now'));
        $vehicle->setGears(5);
        $vehicle->setColor('red');
        $vehicle->setGearbox('automatic');
        $vehicle->setStatus('available');
        $vehicle->setPower(150);
        $vehicle->setType('suv');
        $vehicle->setPrice('150000');
        $manager->persist($vehicle);
        $manager->flush();
    }
}

<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Vehicle;
use App\Repository\CarRentalRepository;
use App\Repository\ImageRepository;
use App\Repository\UserRepository;
use App\Repository\VehicleRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Json;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
 * Class VehicleController
 * @package App\Controller
 * @Route("/vehicles")
 */
class VehicleController extends AbstractController
{
    /**
     * @Route("/", name="vehicle_list", methods={"GET"})
     * @param VehicleRepository $vehicleRepository
     * @return Response
     */
    public function index(VehicleRepository $vehicleRepository): Response
    {
        $vehicles = $vehicleRepository->findAllAsArray();
        if(count($vehicles) == 0) {
            return new JsonResponse('no vehicles', 400);
        }
        return new JsonResponse($vehicles, 200);
    }

    /**
     * @Route("/", name="vehicle_insert", methods={"POST"})
     * @param Request $request
     * @param CarRentalRepository $carRentalRepository
     * @param UserRepository $userRepository
     * @return JsonResponse
     * @throws Exception
     */
    public function insertAction(Request $request, CarRentalRepository $carRentalRepository, UserRepository $userRepository): JsonResponse
    {
        $response = json_decode($request->getContent(), true);
        $vehicle = new Vehicle();
        $vehicle->setMark($response['mark']);
        $vehicle->setModel($response['model']);
        $vehicle->setModelYear(new \DateTime($response['modelYear']));
        $vehicle->setManufactureYear(new \DateTime($response['manufactureYear']));
        $vehicle->setGears($response['gears']);
        $vehicle->setColor($response['color']);
        $vehicle->setGearbox($response['gearbox']);
        $vehicle->setStatus($response['status']);
        $vehicle->setPower($response['power']);
        $vehicle->setType($response['type']);
        $vehicle->setPrice($response['price']);
        $vehicle->setFuelType($response['fuelType']);
        $vehicle->setGateNumber($response['gateNumber']);
        if(isset($response['discount'])) {
            $vehicle->setDiscount($response['discount']);
        }
        $user = $userRepository->findOneBy(['id'=>$response['user_id']]);
        $carRental = $carRentalRepository->findOneBy(['owner'=>$user]);
        $vehicle->setCarRental($carRental);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($vehicle);
        $entityManager->flush();

        foreach($response['images'] as $imageResponse) {
            $image = new Image();
            $image->setIsCover($imageResponse['isCover']);
            $image->setBase64($imageResponse['base64']);
            $image->setVehicle($vehicle);
            $entityManager->persist($image);
            $entityManager->flush();
        }
        return new JsonResponse('success', 200);
    }

    /**
     * @Route("/{id}", name="vehicle_update", methods={"PUT"})
     * @param Request $request
     * @param VehicleRepository $vehicleRepository
     * @param ImageRepository $imageRepository
     * @return JsonResponse
     * @throws Exception
     */
    public function updateAction(Request $request, VehicleRepository $vehicleRepository, ImageRepository $imageRepository): JsonResponse
    {
        $id = $request->get('id');
        $response = json_decode($request->getContent(), true);

        $vehicle = $vehicleRepository->findOneBy(['id'=>$id]);
        $vehicle->setMark($response['mark']);
        $vehicle->setModel($response['model']);
        $vehicle->setModelYear(new \DateTime($response['modelYear']));
        $vehicle->setManufactureYear(new \DateTime($response['manufactureYear']));
        $vehicle->setGears($response['gears']);
        $vehicle->setColor($response['color']);
        $vehicle->setGearbox($response['gearbox']);
        $vehicle->setStatus($response['status']);
        $vehicle->setPower($response['power']);
        $vehicle->setType($response['type']);
        $vehicle->setPrice($response['price']);
        $vehicle->setFuelType($response['fuelType']);
        $vehicle->setGateNumber($response['gateNumber']);
        if(isset($response['discount'])) {
            $vehicle->setDiscount($response['discount']);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($vehicle);
        $entityManager->flush();

        if(isset($response['images'])) {
            $images = $imageRepository->findBy(['vehicle'=>$vehicle]);
            foreach($images as $image) {
                $entityManager->remove($image);
                $entityManager->flush();
            }
            foreach($response['images'] as $imageResponse) {
                $image = new Image();
                $image->setIsCover($imageResponse['isCover']);
                $image->setBase64($imageResponse['base64']);
                $image->setVehicle($vehicle);
                $entityManager->persist($image);
                $entityManager->flush();
            }
        }
        return new JsonResponse('success', 201);
    }

    /**
     * @Route("/{id}", name="vehicle_delete", methods={"DELETE"})
     * @param Request $request
     * @param VehicleRepository $vehicleRepository
     * @return JsonResponse
     */
    public function deleteAction(Request $request, VehicleRepository $vehicleRepository): JsonResponse
    {
        $id = $request->get('id');
        $vehicle = $vehicleRepository->findOneBy(['id'=>$id]);
        if($vehicle === null) {
            return new JsonResponse('vehicle does not exist', 400);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($vehicle);
        $entityManager->flush();
        return new JsonResponse('success', 200);
    }

    /**
     * @Route("/filter", name="vehicle_filter", methods={"POST"})
     * @param Request $request
     * @param VehicleRepository $vehicleRepository
     * @return JsonResponse
     */
    public function filterByLocationAndDatesAction(Request $request, VehicleRepository $vehicleRepository): JsonResponse
    {
        $response = json_decode($request->getContent(), true);
        //$vehicles = $vehicleRepository->filterAvailableByStatusAndLocation($response['location']);
        $vehicles = $vehicleRepository->filterReservedByDatesAndLocation(
          $response['location'], $response['startTime'], $response['endTime']
        );
        if(count($vehicles) == 0) {
            return new JsonResponse('no vehicles', 400);
        }
        return new JsonResponse($vehicles, 200);
    }

    /**
     * @Route("/filter/{id}", name="vehicle_filter_by_carrental", methods={"GET"})
     * @param Request $request
     * @param VehicleRepository $vehicleRepository
     * @return JsonResponse
     */
    public function filterByCarRentalAction(Request $request, VehicleRepository $vehicleRepository): JsonResponse
    {
        $id = $request->get('id');
        $vehicles = $vehicleRepository->filterAvailableVehiclesByCarRental($id);
        if(count($vehicles) == 0) {
            return new JsonResponse('no vehicles', 400);
        }
        return new JsonResponse($vehicles, 400);
    }
}

<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Vehicle;
use App\Repository\CarRentalRepository;
use App\Repository\ImageRepository;
use App\Repository\UserRepository;
use App\Repository\VehicleRepository;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


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
    public function getAllVehiclesAction(VehicleRepository $vehicleRepository): Response
    {
        $vehicles = $vehicleRepository->findAllAsArray();
        if(count($vehicles) == 0) {
            return new JsonResponse('No vehicles.', 400);
        }
        return new JsonResponse($vehicles, 200);
    }

    /**
     * @Route("/", name="vehicle_insert", methods={"POST"})
     * @param Request $request
     * @param CarRentalRepository $carRentalRepository
     * @param UserRepository $userRepository
     * @param JWTEncoderInterface $JWTEncoder
     * @return JsonResponse
     * @throws Exception
     */
    public function insertVehicleAction(Request $request,
                                        CarRentalRepository $carRentalRepository,
                                        UserRepository $userRepository,
                                        JWTEncoderInterface $JWTEncoder): JsonResponse
    {
        $request = json_decode($request->getContent(), true);
        $jwtToken = $JWTEncoder->decode($request['token']);
        $user = $userRepository->findUserByJwtUsername($jwtToken['username']);

        if(!in_array("ROLE_ADMIN", $user->getRoles())) {
            return new JsonResponse('You are not owner of car rental company.', 400);
        }
        $vehicle = new Vehicle();
        $vehicle->setMark($request['mark']);
        $vehicle->setModel($request['model']);
        $vehicle->setModelYear(new \DateTime($request['modelYear']));
        $vehicle->setManufactureYear(new \DateTime($request['manufactureYear']));
        $vehicle->setGears($request['gears']);
        $vehicle->setColor($request['color']);
        $vehicle->setGearbox($request['gearbox']);
        $vehicle->setStatus($request['status']);
        $vehicle->setPower($request['power']);
        $vehicle->setType($request['type']);
        $vehicle->setPrice($request['price']);
        $vehicle->setFuelType($request['fuelType']);
        $vehicle->setGateNumber($request['gateNumber']);
        if(isset($request['discount'])) {
            $vehicle->setDiscount($request['discount']);
        }
        $carRental = $carRentalRepository->findOneBy(['owner'=>$user]);
        $vehicle->setCarRental($carRental);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($vehicle);
        $entityManager->flush();

        foreach($request['images'] as $imageResponse) {
            $image = new Image();
            $image->setIsCover($imageResponse['isCover']);
            $image->setBase64($imageResponse['base64']);
            $image->setVehicle($vehicle);
            $entityManager->persist($image);
            $entityManager->flush();
        }
        return new JsonResponse('Success.', 200);
    }

    /**
     * @Route("/{id}", name="vehicle_update", methods={"PUT"})
     * @param Request $request
     * @param VehicleRepository $vehicleRepository
     * @param ImageRepository $imageRepository
     * @param JWTEncoderInterface $JWTEncoder
     * @param UserRepository $userRepository
     * @return JsonResponse
     * @throws JWTDecodeFailureException
     */
    public function updateVehicleAction(Request $request,
                                        VehicleRepository $vehicleRepository,
                                        ImageRepository $imageRepository,
                                        JWTEncoderInterface $JWTEncoder,
                                        UserRepository $userRepository): JsonResponse
    {
        $id = $request->get('id');
        $request = json_decode($request->getContent(), 1);
        $jwtToken = $JWTEncoder->decode($request['token']);
        $user = $userRepository->findUserByJwtUsername($jwtToken['username']);

        $vehicle = $vehicleRepository->findOneBy(['id'=>$id]);
        $carRental = $vehicle->getCarRental();
        $owner = $carRental->getOwner();
        if($user->getId() !== $owner->getId()) {
            return new JsonResponse("You are not owner of this car rental company.", 400);
        }
        $vehicle->setMark($request['mark']);
        $vehicle->setModel($request['model']);
        $vehicle->setModelYear(new \DateTime($request['modelYear']));
        $vehicle->setManufactureYear(new \DateTime($request['manufactureYear']));
        $vehicle->setGears($request['gears']);
        $vehicle->setColor($request['color']);
        $vehicle->setGearbox($request['gearbox']);
        $vehicle->setStatus($request['status']);
        $vehicle->setPower($request['power']);
        $vehicle->setType($request['type']);
        $vehicle->setPrice($request['price']);
        $vehicle->setFuelType($request['fuelType']);
        $vehicle->setGateNumber($request['gateNumber']);
        if(isset($request['discount'])) {
            $vehicle->setDiscount($request['discount']);
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
        return new JsonResponse('Success.', 200);
    }

    /**
     * @Route("/{id}", name="vehicle_delete", methods={"DELETE"})
     * @param Request $request
     * @param VehicleRepository $vehicleRepository
     * @param JWTEncoderInterface $JWTEncoder
     * @param UserRepository $userRepository
     * @return JsonResponse
     * @throws JWTDecodeFailureException
     */
    public function deleteVehicleAction(Request $request,
                                        VehicleRepository $vehicleRepository,
                                        JWTEncoderInterface $JWTEncoder,
                                        UserRepository $userRepository): JsonResponse
    {
        $id = $request->get('id');
        $request = json_decode($request->getContent(), 1);
        $jwtToken = $JWTEncoder->decode($request['token']);
        $user = $userRepository->findUserByJwtUsername($jwtToken['username']);

        $vehicle = $vehicleRepository->findOneBy(['id'=>$id]);
        if($vehicle === null) {
            return new JsonResponse('Vehicle with id '.$id.' does not exist.', 400);
        }
        $carRental = $vehicle->getCarRental();
        $owner = $carRental->getOwner();
        if($user->getId() !== $owner->getId()) {
            return new JsonResponse("You are not owner of this car rental company.", 400);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($vehicle);
        $entityManager->flush();
        return new JsonResponse('Success.', 200);
    }

    /**
     * @Route("/filter", name="vehicle_filter_by_location_and_dates", methods={"POST"})
     * @param Request $request
     * @param VehicleRepository $vehicleRepository
     * @return JsonResponse
     */
    public function filterByLocationAndDatesAction(Request $request,
                                                   VehicleRepository $vehicleRepository): JsonResponse
    {
        $request = json_decode($request->getContent(), true);
        $vehicles1 = $vehicleRepository->filterAvailableByStatusAndLocation($request['location']);
        $vehicles2 = $vehicleRepository->filterReservedByDatesAndLocation(
            $request['location'], $request['startTime'], $request['endTime']
        );
        $vehicles = array_merge($vehicles1, $vehicles2);
        if(count($vehicles) == 0) {
            return new JsonResponse('No vehicles.', 400);
        }
        return new JsonResponse($vehicles, 200);
    }

    /**
     * @Route("/filter/{id}", name="vehicle_filter_by_carrental", methods={"GET"})
     * @param Request $request
     * @param VehicleRepository $vehicleRepository
     * @return JsonResponse
     */
    public function filterByCarRentalAction(Request $request,
                                            VehicleRepository $vehicleRepository): JsonResponse
    {
        $id = $request->get('id');
        $vehicles = $vehicleRepository->filterAvailableVehiclesByCarRental($id);
        if(count($vehicles) == 0) {
            return new JsonResponse('No vehicles found in car rental company with id '.$id.'.', 400);
        }
        return new JsonResponse($vehicles, 200);
    }

    /**
     * @Route("/get/{id}", name="vehicle_filter_by_id", methods={"GET"})
     * @param Request $request
     * @param VehicleRepository $vehicleRepository
     * @return JsonResponse
     */
    public function getVehicleByIdAction(Request $request,
                                         VehicleRepository $vehicleRepository): JsonResponse
    {
        $id = $request->get('id');
        $vehicle = $vehicleRepository->findOneAsArray($id);
        if(count($vehicle) == 0) {
            return new JsonResponse('Vehicle with id '.$id.' does not exist.', 400);
        }
        return new JsonResponse($vehicle, 200);
    }
}

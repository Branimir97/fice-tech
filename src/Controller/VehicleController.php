<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Vehicle;
use App\Exceptions\CreateVehicleException;
use App\Repository\VehicleRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Json;

class VehicleController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param VehicleRepository $vehicleRepository
     * @return Response
     */
    public function index(VehicleRepository $vehicleRepository): Response
    {
        $vehicles = $vehicleRepository->findAllAsArray();
        return new JsonResponse($vehicles, 200);
    }

    /**
     * @Route("/new", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function createAction(Request $request): JsonResponse
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
     * @Route("/update/{id}")
     * @param Request $request
     * @param VehicleRepository $vehicleRepository
     * @throws Exception
     */
    public function updateAction(Request $request, VehicleRepository $vehicleRepository) {
        $id = $request->get('id');

        $mark = $request->query->get('mark');
        $model = $request->query->get('model');
        $modelYear = new \DateTime($request->query->get('modelYear'));
        $manufactureYear = new \DateTime($request->query->get('manufactureYear'));
        $gears = $request->query->get('gears');
        $color = $request->query->get('color');
        $gearbox = $request->query->get('gearbox');
        $power = $request->query->get('power');
        $type = $request->query->get('type');
        $status = $request->query->get('status');
        $price = $request->query->get('price');

        $vehicle = $vehicleRepository->findOneBy(['id'=>$id]);
        $vehicle->constructObject($mark, $model ,$modelYear, $manufactureYear, $gears, $color, $gearbox, $power, $type, $status, $price);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($vehicle);
        $entityManager->flush();

        return new JsonResponse('success', 201);
    }

    /**
     * @Route("/delete/{id}")
     * @param Request $request
     * @param VehicleRepository $vehicleRepository
     * @return JsonResponse
     */
    public function deleteAction(Request $request, VehicleRepository $vehicleRepository): JsonResponse
    {
        $id = $request->get('id');
        $vehicle = $vehicleRepository->findOneBy(['id'=>$id]);
        if($vehicle === null) {
            return new JsonResponse('vehicle does not exists', 400);
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($vehicle);
            $entityManager->flush();
            return new JsonResponse('success', 200);
        }
    }
}

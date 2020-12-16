<?php

namespace App\Controller;

use App\Entity\Vehicle;
use App\Exceptions\CreateVehicleException;
use App\Repository\VehicleRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/new")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function createAction(Request $request): JsonResponse
    {
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

        $vehicle = new Vehicle();
        $vehicle->constructObject($mark, $model ,$modelYear, $manufactureYear, $gears, $color, $gearbox, $power, $type, $status, $price);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($vehicle);
        $entityManager->flush();

        return new JsonResponse('success', 201);
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
            return new JsonResponse('success', 200);
        }
    }
}

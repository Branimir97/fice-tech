<?php

namespace App\Controller;

use App\Entity\Vehicle;
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
        $response = new JsonResponse($vehicles, 200);

        return $response;
    }

    /**
     * @Route("/new")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(Request $request) {
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
        $entityManager= $this->getDoctrine()->getManager();
        $entityManager->persist($vehicle);
        $entityManager->flush();

        return new JsonResponse('success', 200);
    }
}

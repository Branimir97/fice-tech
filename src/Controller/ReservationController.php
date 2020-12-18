<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ReservationController
 * @package App\Controller
 * @Route("/reservation")
 */
class ReservationController extends AbstractController
{
    /**
     * @Route("/", name="reservation_list", methods={"GET"})
     * @param ReservationRepository $reservationRepository
     * @return Response
     */
    public function index(ReservationRepository $reservationRepository): Response
    {
       $reservations = $reservationRepository->findAllAsArray();
       if(count($reservations) == 0) {
           return new JsonResponse('no reservations', 400);
       }
       return new JsonResponse($reservations, 200);
    }
}

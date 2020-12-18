<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\User;
use App\Repository\ReservationRepository;
use App\Repository\VehicleRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @Route("/approved", name="reservation_list_approved", methods={"GET"})
     * @param ReservationRepository $reservationRepository
     * @return Response
     */
    public function getApprovedReservationsAction(ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findAllApproved();
        if(count($reservations) == 0) {
            return new JsonResponse('reservations not found', 400);
        }
        return new JsonResponse($reservations, 200);
    }

    /**
     * @Route("/notapproved", name="reservation_list_notapproved", methods={"GET"})
     * @param ReservationRepository $reservationRepository
     * @return Response
     */
    public function getNotApprovedReservationsAction(ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findAllNotApproved();
        if(count($reservations) == 0) {
            return new JsonResponse('reservations not found', 400);
        }
        return new JsonResponse($reservations, 200);
    }

    /**
     * @Route("/insert", name="reservation_insert", methods={"POST"})
     * @param Request $request
     * @param VehicleRepository $vehicleRepository
     * @return JsonResponse
     * @throws Exception
     */
    public function insertAction(Request $request, VehicleRepository $vehicleRepository): JsonResponse
    {
        $response = json_decode($request->getContent(), true);
        $vehicle = $vehicleRepository->findOneBy(["id"=>$response['vehicle']]);
        $reservation = new Reservation();
        $reservation->setUser();
        $reservation->setVehicle($vehicle);
        $reservation->setStartTime(new \DateTime($response['startTime']));
        $reservation->setEndTime((new \DateTime($response['endTime'])));
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($reservation);
        $entityManager->flush();

        return new JsonResponse('success', 200);
    }

    /**
     * @Route("/delete/{id}", name="reservation_delete", methods={"DELETE"})
     * @param Request $request
     * @param ReservationRepository $reservationRepository
     * @return JsonResponse
     */
    public function deleteAction(Request $request, ReservationRepository $reservationRepository): JsonResponse
    {
        $id = $request->get('id');
        $reservation = $reservationRepository->findOneBy(['id'=>$id]);
        if($reservation === null) {
            return new JsonResponse('reservation does not exist', 400);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($reservation);
        $entityManager->flush();
        return new JsonResponse('success', 200);
    }

    public function changeReservationApprovalAction(Request $request) {

    }
}

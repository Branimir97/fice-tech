<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\User;
use App\Repository\CarRentalRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use App\Repository\VehicleRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
 * Class ReservationController
 * @package App\Controller
 * @Route("/reservations")
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
     * @Route("/{id}", name="reservation_insert", methods={"POST"})
     * @param Request $request
     * @param VehicleRepository $vehicleRepository
     * @param CarRentalRepository $carRentalRepository
     * @param UserRepository $userRepository
     * @return JsonResponse
     * @throws Exception
     */
    public function insertAction(Request $request, VehicleRepository $vehicleRepository, CarRentalRepository $carRentalRepository, UserRepository $userRepository): JsonResponse
    {
        $id = $request->get('id');
        $response = json_decode($request->getContent(), true);
        $vehicle = $vehicleRepository->findOneBy(["id"=>$id]);
        $reservation = new Reservation();
        $user = $userRepository->findOneBy(['id'=>$response['user_id']]);
        $reservation->setUser($user);
        $reservation->setVehicle($vehicle);
        $reservation->setStartTime(new \DateTime($response['startTime']));
        $reservation->setEndTime((new \DateTime($response['endTime'])));
        $reservation->setPaymentMethod($response['paymentMethod']);
        if($response['paymentMethod']=='cash') {
            if(isset($response['paymentAmount']))
                $reservation->setPaymentAmount($response['paymentAmount']);
            else {
                return new JsonResponse('payment amount must be defined if user pays with cash', 400);
            }
        }
        $reservation->setStatus("waiting");
        $carRental = $carRentalRepository->findOneBy(['id'=>$response['carRental']]);
        if($carRental === null) {
            return new JsonResponse('car rental not found', 400);
        }
        $reservation->addCarRental($carRental);
        $reservation->setInfo($response['info']);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($reservation);
        $entityManager->flush();

        return new JsonResponse('success', 200);
    }

    /**
     * @Route("/{id}", name="reservation_delete", methods={"DELETE"})
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

    /**
     * @Route("/{id}", name="reservation_list_by_user", methods={"GET"})
     * @param Request $request
     * @param ReservationRepository $reservationRepository
     * @return JsonResponse
     */
    public function getByUserAction(Request $request, ReservationRepository $reservationRepository): JsonResponse
    {
        $id = $request->get('id');
        $reservations = $reservationRepository->findByUserId($id);

        if(count($reservations) == 0) {
            return new JsonResponse('no reservations', 400);
        }
        return new JsonResponse($reservations, 200);
    }

    /**
     * @param Request $request
     * @param ReservationRepository $reservationRepository
     * @return JsonResponse
     * @Route("/update/{id}", name="reservation_update", methods={"PUT"})
     */
    public function changeReservationStatus(Request $request, ReservationRepository $reservationRepository): JsonResponse
    {
        $id = $request->get('id');
        $response = json_decode($request->getContent(), true);
        $reservation = $reservationRepository->findOneBy(["id"=>$id]);
        if($reservation === null) {
            return new JsonResponse('reservation does not exist', 200);
        }
        $allowedStatus = ['accepted', 'rejected', 'waiting'];
        if(!in_array($response['status'], $allowedStatus)) {
           return new JsonResponse('not allowed status name', 400);
        } else {
            if($reservation->getStatus() == $response['status']) {
                return new JsonResponse('reservation is already '.$response['status'], 200);
            }
        }
        $vehicle = $reservation->getVehicle();
        $reservation->setStatus($response['status']);
        if($reservation->getStatus()==="accepted") {
            $vehicle->setStatus("Reserved");
        } else if($reservation->getStatus() == "rejected" || $reservation->getStatus() == "waiting") {
            $vehicle->setStatus("Available");
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($vehicle);
        $entityManager->persist($reservation);
        $entityManager->flush();
        return new JsonResponse('success', 200);
    }
}

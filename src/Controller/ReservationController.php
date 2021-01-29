<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\CarRentalRepository;
use App\Repository\ReservationRepository;
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
 * Class ReservationController
 * @package App\Controller
 * @Route("/reservations")
 */
class ReservationController extends AbstractController
{
    /**
     * @Route("/", name="reservation_list", methods={"POST"})
     * @param Request $request
     * @param ReservationRepository $reservationRepository
     * @param JWTEncoderInterface $JWTEncoder
     * @return Response
     * @throws JWTDecodeFailureException
     */
    public function getAllReservationsAction(Request $request, ReservationRepository $reservationRepository,
                          JWTEncoderInterface $JWTEncoder): Response
    {
        $request = json_decode($request->getContent(), 1);
        $JWTEncoder->decode($request['token']);
        $reservations = $reservationRepository->findAllAsArray();
         if(count($reservations) == 0) {
           return new JsonResponse('No reservations.', 400);
        }
        return new JsonResponse($reservations, 200);
    }

    /**
     * @Route("/{id}", name="reservation_insert", methods={"POST"})
     * @param Request $request
     * @param VehicleRepository $vehicleRepository
     * @param CarRentalRepository $carRentalRepository
     * @param UserRepository $userRepository
     * @param JWTEncoderInterface $JWTEncoder
     * @return JsonResponse
     * @throws Exception
     */
    public function insertReservationAction(Request $request, VehicleRepository $vehicleRepository,
                                 CarRentalRepository $carRentalRepository,
                                 UserRepository $userRepository, JWTEncoderInterface $JWTEncoder): JsonResponse
    {
        $id = $request->get('id');
        $request = json_decode($request->getContent(), true);
        $jwtToken = $JWTEncoder->decode($request['token']);

        $reservation = new Reservation();
        $user = $userRepository->findUserByJwtUsername($jwtToken['username']);
        $reservation->setUser($user);
        $vehicle = $vehicleRepository->findOneBy(["id"=>$id]);
        if($vehicle === null) {
            return new JsonResponse('There is no vehicle with id '.$id.'.', 400);
        }
        $reservation->setVehicle($vehicle);
        $reservation->setStartTime(new \DateTime($request['startTime']));
        $reservation->setEndTime((new \DateTime($request['endTime'])));
        $reservation->setPaymentMethod($request['paymentMethod']);
        if($request['paymentMethod']=='cash') {
            if(isset($request['paymentAmount']))
                $reservation->setPaymentAmount($request['paymentAmount']);
            else {
                return new JsonResponse('Payment amount must be defined if user pays with cash.', 400);
            }
        }
        $reservation->setStatus("Waiting");
        $carRental = $carRentalRepository->findOneBy(['id'=>$request['carRental']]);
        if($carRental === null) {
            return new JsonResponse('Car-rental house with id '.$request['carRental'].' not found.', 400);
        }
        $reservation->addCarRental($carRental);
        $reservation->setInfo($request['info']);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($reservation);
        $entityManager->flush();

        return new JsonResponse('Success.', 200);
    }

    /**
     * @Route("/{id}", name="reservation_delete", methods={"DELETE"})
     * @param Request $request
     * @param ReservationRepository $reservationRepository
     * @param JWTEncoderInterface $JWTEncoder
     * @param UserRepository $userRepository
     * @return JsonResponse
     * @throws JWTDecodeFailureException
     */
    public function deleteReservationAction(Request $request, ReservationRepository $reservationRepository,
                                            JWTEncoderInterface $JWTEncoder, UserRepository $userRepository): JsonResponse
    {
        $id = $request->get('id');
        $request = json_decode($request->getContent(), 1);
        $jwtToken = $JWTEncoder->decode($request['token']);
        $user = $userRepository->findUserByJwtUsername($jwtToken['username']);

        $reservation = $reservationRepository->findOneBy(['id'=>$id]);
        if($reservation === null) {
            return new JsonResponse('Reservation with id '.$id.' does not exist.', 400);
        }
        $vehicle = $reservation->getVehicle();
        $carRental = $vehicle->getCarRental();
        $owner = $carRental->getOwner();
        if($user->getId() !== $owner->getId()) {
            return new JsonResponse("You are not owner of this car rental company.", 400);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($reservation);
        $entityManager->flush();
        return new JsonResponse('Success.', 200);
    }

    /**
     * @Route("/user/{id}", name="reservation_list_by_user", methods={"POST"})
     * @param Request $request
     * @param ReservationRepository $reservationRepository
     * @param JWTEncoderInterface $JWTEncoder
     * @return JsonResponse
     * @throws JWTDecodeFailureException
     */
    public function getReservationByUserIdAction(Request $request, ReservationRepository $reservationRepository,
                                      JWTEncoderInterface $JWTEncoder): JsonResponse
    {
        $id = $request->get('id');
        $request = json_decode($request->getContent(), 1);
        $JWTEncoder->decode($request['token']);
        $reservations = $reservationRepository->findByUserId($id);
        if(count($reservations) == 0) {
            return new JsonResponse('No reservations.', 400);
        }
        return new JsonResponse($reservations, 200);
    }

    /**
     * @param Request $request
     * @param ReservationRepository $reservationRepository
     * @param JWTEncoderInterface $JWTEncoder
     * @param UserRepository $userRepository
     * @return JsonResponse
     * @throws JWTDecodeFailureException
     * @Route("/update/{id}", name="reservation_update", methods={"PUT"})
     */
    public function changeReservationStatusAction(Request $request, ReservationRepository $reservationRepository,
                                            JWTEncoderInterface $JWTEncoder, UserRepository $userRepository): JsonResponse
    {
        $id = $request->get('id');
        $request = json_decode($request->getContent(), true);
        $jwtToken = $JWTEncoder->decode($request['token']);
        $user = $userRepository->findUserByJwtUsername($jwtToken['username']);

        $reservation = $reservationRepository->findOneBy(["id"=>$id]);
        $vehicle = $reservation->getVehicle();
        $carRental = $vehicle->getCarRental();
        $owner = $carRental->getOwner();
        if($user->getId() !== $owner->getId()) {
            return new JsonResponse("You are not owner of this car rental company.", 400);
        }
        if($reservation === null) {
            return new JsonResponse('Reservation does not exist.', 400);
        }
        $allowedStatus = ['Accepted', 'Rejected', 'Waiting'];
        if(!in_array($request['status'], $allowedStatus)) {
           return new JsonResponse('You need to enter one of these statuses: [Accepted, Rejected, Waiting].', 400);
        } else {
            if($reservation->getStatus() == $request['status']) {
                return new JsonResponse('Reservation is already '.$request['status'].'.', 400);
            }
        }
        $vehicle = $reservation->getVehicle();
        $reservation->setStatus($request['status']);
        if($reservation->getStatus()==="Accepted") {
            $vehicle->setStatus("Reserved");
        } else if($reservation->getStatus() == "Rejected" || $reservation->getStatus() == "Waiting") {
            $vehicle->setStatus("Available");
        }
        $reservation->setInfo($request['info']);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($vehicle);
        $entityManager->persist($reservation);
        $entityManager->flush();
        return new JsonResponse('Success.', 200);
    }
}

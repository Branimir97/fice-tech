<?php

namespace App\Controller;

use App\Entity\CarRental;
use App\Repository\CarRentalRepository;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Json;

/**
 * Class CarRentalController
 * @package App\Controller
 * @Route("/carrental")
 */
class CarRentalController extends AbstractController
{
    /**
     * @Route("/", name="carrental_insert", methods={"POST"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param JWTEncoderInterface $JWTEncoder
     * @return JsonResponse
     * @throws JWTDecodeFailureException
     */
    public function insertCarRentalAction(Request $request, UserRepository $userRepository,
                                          JWTEncoderInterface $JWTEncoder): JsonResponse
    {
        $request = json_decode($request->getContent(), true);
        $jwtToken = $JWTEncoder->decode($request['token']);
        $user = $userRepository->findUserByJwtUsername($jwtToken['username']);
        if(in_array("ROLE_ADMIN", $user->getRoles())) {
            return new JsonResponse('You already registered car rental company.', 400);
        }
        $user->setRoles(array("ROLE_USER", "ROLE_ADMIN"));
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $carRental = new CarRental();
        $carRental->setName($request['name']);
        $carRental->setOwner($user);
        $carRental->setCity($request['city']);
        $carRental->setAddress($request['address']);
        $carRental->setContactNumber($request['contactNumber']);
        $carRental->setEmail($request['email']);
        $carRental->setImage($request['image']);

        $entityManager->persist($carRental);
        $entityManager->flush();
        return new JsonResponse('Success.', 200);
    }

    /**
     * @Route("/{id}", name="carrental_delete", methods={"DELETE"})
     * @param Request $request
     * @param CarRentalRepository $carRentalRepository
     * @param JWTEncoderInterface $JWTEncoder
     * @param UserRepository $userRepository
     * @return JsonResponse
     * @throws JWTDecodeFailureException
     */
    public function deleteCarRentalAction(Request $request, CarRentalRepository $carRentalRepository,
                                          JWTEncoderInterface $JWTEncoder, UserRepository $userRepository): JsonResponse
    {
        $id = $request->get('id');
        $request = json_decode($request->getContent(), 1);
        $jwtToken = $JWTEncoder->decode($request['token']);
        $user = $userRepository->findUserByJwtUsername($jwtToken['username']);

        $carRental = $carRentalRepository->findOneBy(['id'=>$id]);
        if($carRental->getId() !== $user->getId()) {
            return new JsonResponse('You are not owner of this car rental company.', 400);
        }
        if($carRental === null) {
            return new JsonResponse('Car rental company with id '.$id.' does not exist.', 400);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($carRental);
        $user->setRoles(array("ROLE_USER"));
        $entityManager->persist($user);
        $vehicles = $carRental->getVehicles();
        foreach ($vehicles as $vehicle) {
            $images = $vehicle->getImages();
            foreach($images as $image) {
                $entityManager->remove($image);
            }
            $reservations = $vehicle->getReservations();
            foreach($reservations as $reservation) {
                $entityManager->remove($reservation);
            }
        }
        foreach ($carRental->getVehicles() as $vehicle) {
            $entityManager->remove($vehicle);
    }
        $entityManager->flush();
        return new JsonResponse('Success.', 200);
    }

    /**
     * @Route("/{id}", name="carrental_get", methods={"POST"})
     * @param Request $request
     * @param CarRentalRepository $carRentalRepository
     * @param JWTEncoderInterface $JWTEncoder
     * @return JsonResponse
     */
    public function getCarRentalByIdAction(Request $request, CarRentalRepository $carRentalRepository): JsonResponse
    {
        $id = $request->get('id');
        $carRentalCompany = $carRentalRepository->findOneById($id);
        if(empty($carRentalCompany)) {
            return new JsonResponse('No car rental company found by id '.$id.'.', 400);
        }
        return new JsonResponse($carRentalCompany, 200);
    }
}

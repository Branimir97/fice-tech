<?php

namespace App\Controller;

use App\Entity\CarRental;
use App\Entity\User;
use App\Repository\CarRentalRepository;
use App\Repository\UserRepository;
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
     * @return JsonResponse
     */
    public function insertAction(Request $request, UserRepository $userRepository): JsonResponse
    {
        $response = json_decode($request->getContent(), true);
        $user = $userRepository->findOneBy(['id'=>$response['user']]);
        $user->setRoles(array("ROLE_USER", "ROLE_ADMIN"));
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $carRental = new CarRental();
        $carRental->setName($response['name']);
        $carRental->setOwner($user);
        $carRental->setCity($response['city']);
        $carRental->setAddress($response['address']);
        $carRental->setContactNumber($response['contactNumber']);
        $carRental->setEmail($response['email']);
        $carRental->setImage($response['image']);

        $entityManager->persist($carRental);
        $entityManager->flush();
        return new JsonResponse('success', 200);
    }

    /**
     * @Route("/{id}", name="carrental_delete", methods={"DELETE"})
     * @param Request $request
     * @param CarRentalRepository $carRentalRepository
     * @return JsonResponse
     */
    public function deleteAction(Request $request, CarRentalRepository $carRentalRepository): JsonResponse
    {
        $id = $request->get('id');
        $carRentalCompany = $carRentalRepository->findOneBy(['id'=>$id]);
        if($carRentalCompany === null) {
            return new JsonResponse('car rental company does not exist', 400);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($carRentalCompany);
        $entityManager->flush();
        return new JsonResponse('success', 200);
    }

    /**
     * @Route("/{id}", name="carrental_get", methods={"GET"})
     * @param Request $request
     * @param CarRentalRepository $carRentalRepository
     * @return JsonResponse
     */
    public function getByIdAction(Request $request, CarRentalRepository $carRentalRepository): JsonResponse
    {
        $id = $request->get('id');
        $carRentalCompany = $carRentalRepository->findOneById($id);
        if(empty($carRentalCompany)) {
            return new JsonResponse('no car rental company found by id '.$id, 400);
        }
        return new JsonResponse($carRentalCompany, 200);
    }
}

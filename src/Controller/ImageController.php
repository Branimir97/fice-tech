<?php

namespace App\Controller;

use App\Entity\Image;
use App\Repository\ImageRepository;
use App\Repository\VehicleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ImageController
 * @package App\Controller
 * @Route("/image")
 */
class ImageController extends AbstractController
{
    /**
     * @Route("/delete/{id}", name="image_delete", methods={"DELETE"})
     * @param Request $request
     * @param ImageRepository $imageRepository
     * @return JsonResponse
     */
    public function deleteAction(Request $request, ImageRepository $imageRepository): JsonResponse
    {
        $id = $request->get('id');
        $image = $imageRepository->findOneBy(['id'=>$id]);
        if($image === null) {
            return new JsonResponse('image does not exist', 400);
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($image);
            $entityManager->flush();
            return new JsonResponse('success', 200);
        }
    }

    /**
     * @Route("/insert/{id}", name="", methods={"PUT"})
     * @param Request $request
     * @param VehicleRepository $vehicleRepository
     * @return JsonResponse
     */
    public function insertAction(Request $request, VehicleRepository $vehicleRepository): JsonResponse
    {
        $id = $request->get('id');
        $response = json_decode($request->getContent(), true);
        $vehicle = $vehicleRepository->findOneBy(['id'=>$id]);
        $entityManager = $this->getDoctrine()->getManager();
        foreach ($response['images'] as $imageResponse) {
            $image = new Image();
            $image->setIsCover($imageResponse['isCover']);
            $image->setBase64($imageResponse['base64']);
            $image->setVehicle($vehicle);
            $entityManager->persist($image);
            $entityManager->flush();
        }
        return new JsonResponse('success', 200);
    }
}

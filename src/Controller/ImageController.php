<?php

namespace App\Controller;

use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Json;

/**
 * Class ImageController
 * @package App\Controller
 * @Route("/images")
 */
class ImageController extends AbstractController
{
    /**
     * @Route("/cover/unset/{id}", name="cover_unset", methods={"PATCH"})
     * @param Request $request
     * @param ImageRepository $imageRepository
     * @return JsonResponse
     */
    public function unsetCover(Request $request,
                               ImageRepository $imageRepository): JsonResponse
    {
        $id = $request->get('id');
        $image = $imageRepository->findOneBy(['id'=>$id]);
        if(!$image->getIsCover()) {
            return new JsonResponse('image is not cover', 400);
        } else {
            $image->setIsCover(false);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($image);
            $entityManager->flush();
            return new JsonResponse('success', 200);
        }
    }

    /**
     * @Route("/cover/set/{id}", name="cover_set", methods={"PATCH"})
     * @param Request $request
     * @param ImageRepository $imageRepository
     * @return JsonResponse
     */
    public function setCover(Request $request,
                             ImageRepository $imageRepository): JsonResponse
    {
        $id = $request->get('id');
        $image = $imageRepository->findOneBy(['id'=>$id]);
        if($image->getIsCover()) {
            return new JsonResponse('image is already set as cover', 400);
        } else {
            $image->setIsCover(true);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($image);
            $entityManager->flush();
            return new JsonResponse('success', 200);
        }
    }
}

<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param UserRepository $userRepository
     * @return Response
     * @throws Exception
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepository): Response
    {
        $response = json_decode($request->getContent(), true);

        foreach($userRepository->findAll() as $user) {
            if($user->getEmail() == $response['email']) {
                return new JsonResponse('user already exists)', 400);
            }
        }
        $user = new User();
        $user->setFirstName($response['firstName']);
        $user->setLastName($response['lastName']);
        $user->setBirthday(new \DateTime($response['birthday']));
        $user->setEmail($response['email']);
        $user->setPassword($passwordEncoder->encodePassword($user, $response['password']));
        $user->setRoles(['ROLE_USER']);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse('success', 200);
    }
}

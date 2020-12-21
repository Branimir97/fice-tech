<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\Json;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login", methods={"POST"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return JsonResponse
     * @throws JWTEncodeFailureException
     */
    public function login(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder, JWTEncoderInterface $JWTEncoder): JsonResponse
    {
        $response = json_decode($request->getContent(), 1);
        $user = $userRepository->findOneBy(["email"=>$response['email']]);
        if (!$user) {
            throw $this->createNotFoundException();
        }
        $isValid = $passwordEncoder
            ->isPasswordValid($user, $response['password']);
        if (!$isValid) {
            throw new BadCredentialsException();
        }

        $token = $JWTEncoder
            ->encode([
                'username' => $user->getFirstName(),
                'exp' => time() + 3600 // 1 hour expiration
            ]);
        return new JsonResponse(['token' => $token]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}

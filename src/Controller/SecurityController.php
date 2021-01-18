<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Entity\Token as TokenEntity;
use Lcobucci\JWT\Token;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\Json;

class SecurityController extends AbstractController
{

    private $token;
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

        $this->token = $JWTEncoder
            ->encode([
                'username' => $user->getFirstName(),
                'exp' => time() + 3600 // 1 hour expiration
            ]);

        $entityManager = $this->getDoctrine()->getManager();
        $token = new TokenEntity();
        $token->setValue($this->token);
        $entityManager->persist($token);
        $entityManager->flush();

        return new JsonResponse(['token'=>$this->token]);
    }

    /**
      * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}

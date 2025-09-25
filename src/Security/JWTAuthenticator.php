<?php

namespace App\Security;

use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class JWTAuthenticator extends AbstractAuthenticator
{
    private JWTService $jwtService;
    private ParticipantRepository $participantRepository;

    public function __construct(JWTService $jwtService, ParticipantRepository $participantRepository)
    {
        $this->jwtService = $jwtService;
        $this->participantRepository = $participantRepository;
    }

    public function supports(Request $request): ?bool
    {
        // Vérifier s'il y a un cookie JWT
        return $request->cookies->has('jwt_token');
    }

    public function authenticate(Request $request): Passport
    {
        $token = $request->cookies->get('jwt_token');
        if (!$token) {
            throw new CustomUserMessageAuthenticationException('No JWT token found');
        }

        $payload = $this->jwtService->validateToken($token);
        if (!$payload) {
            throw new CustomUserMessageAuthenticationException('Invalid JWT token');
        }

        $pseudo = $payload['pseudo'] ?? null;
        if (!$pseudo) {
            throw new CustomUserMessageAuthenticationException('No user identifier in token');
        }

        return new SelfValidatingPassport(
            new UserBadge($pseudo, function($userIdentifier) {
                return $this->participantRepository->findOneBy(['pseudo' => $userIdentifier]);
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Ne rien faire, laisser la requête continuer
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // En cas d'échec, supprimer le cookie invalide et continuer sans authentification
        $response = new Response();
        $response->headers->clearCookie('jwt_token');
        return null; // Ne pas bloquer, juste ne pas authentifier
    }
}
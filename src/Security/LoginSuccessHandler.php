<?php

namespace App\Security;

use App\Entity\Participant;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private JWTService $jwtService;
    private RouterInterface $router;

    public function __construct(JWTService $jwtService, RouterInterface $router)
    {
        $this->jwtService = $jwtService;
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();

        if ($user instanceof Participant) {
            $jwtToken = $this->jwtService->createToken($user);

            $response = new RedirectResponse($this->router->generate('app_sortie_index'));
            $response->headers->setCookie(
                new Cookie(
                    'jwt_token',
                    $jwtToken,
                    time() + 86400, // 24h
                    '/',
                    null,
                    false, // secure (false pour dev)
                    true   // httpOnly
                )
            );

            return $response;
        }

        return new RedirectResponse($this->router->generate('app_sortie_index'));
    }
}
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
            // Récupérer la valeur "remember me"
            $rememberMe = $request->request->getBoolean('_remember_me', false);

            // Créer le token avec la durée appropriée
            $jwtToken = $this->jwtService->createTokenWithRememberMe($user, $rememberMe);

            $response = new RedirectResponse($this->router->generate('app_sortie_index'));

            $cookieExpiry = $rememberMe ? time() + 2592000 : time() + 86400; // 30 jours ou 24h

            $response->headers->setCookie(
                new Cookie(
                    'jwt_token',
                    $jwtToken,
                    $cookieExpiry,
                    '/',
                    null,
                    false,
                    true
                )
            );

            return $response;
        }

        return new RedirectResponse($this->router->generate('app_sortie_index'));
    }
}
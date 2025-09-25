<?php

namespace App\Security;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    private RouterInterface $router;
    private Security $security;

    public function __construct(RouterInterface $router, Security $security)
    {
        $this->router = $router;
        $this->security = $security;
    }

    public function handle(Request $request, AccessDeniedException $exception): Response
    {
        // Si l'utilisateur n'est pas connecté → rediriger vers login
        if (!$this->security->getUser()) {
            return new RedirectResponse($this->router->generate('app_login'));
        }

        // Si connecté mais pas les droits → rediriger vers accueil
        return new RedirectResponse($this->router->generate('app_sortie_index'));
    }
}
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/error')]
class ErrorController extends AbstractController
{
    #[Route(name: 'error_page', methods: ['GET'])]
    public function index(): Response
    {

        return $this->render('error/error.html.twig', [
            'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.',
        ]);
    }
}
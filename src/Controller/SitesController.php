<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sites', name: 'app_sites_')]
final class SitesController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function index(): Response
    {
        return $this->render('sites/index.html.twig', [
            'controller_name' => 'SitesController',
        ]);
    }
}

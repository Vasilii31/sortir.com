<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Service\InscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/inscription')]

final class InscriptionController extends AbstractController
{
    #[Route('/sortie/{id}/inscrire', name: 'app_sortie_inscrire', methods: ['POST'])]
    public function inscrire(Sortie $sortie, InscriptionService $inscriptionService, EntityManagerInterface $em): RedirectResponse
    {
        $user = $this->getUser();

        if (!$user instanceof Participant) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour vous inscrire.');
        }

        $inscriptionService->registerParticipant($sortie, $user);

        $em->flush();

        $this->addFlash('success', 'Vous êtes inscrit à la sortie !');

        return $this->redirectToRoute('app_sortie_index');
    }


    #[Route('/sortie/{id}/desister', name: 'app_sortie_desister', methods: ['POST'])]
    public function desister(Sortie $sortie, InscriptionService $inscriptionService, EntityManagerInterface $em): RedirectResponse
    {
        $user = $this->getUser();

        if (!$user instanceof Participant) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour vous inscrire.');
        }

        $inscriptionService->unregisterParticipant($sortie, $user);

        $em->flush();

        $this->addFlash('success', 'Vous êtes désister de la sortie !');

        return $this->redirectToRoute('app_sortie_index');
    }
}

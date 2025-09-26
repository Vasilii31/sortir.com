<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use App\Service\ParticipantService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('/users', name: 'admin_users')]
    public function users(ParticipantService $participantService): Response
    {
        // Vérifier que l'utilisateur est admin
        $currentUser = $this->getUser();
        if (!$currentUser || !$currentUser->isAdministrateur()) {
            throw $this->createAccessDeniedException('Accès réservé aux administrateurs');
        }

        $users = $participantService->getAllParticipants();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'currentUser' => $currentUser,
        ]);
    }

    #[Route('/users/{id}/toggle-admin', name: 'admin_toggle_admin', methods: ['POST'])]
    public function toggleAdmin(
        int $id,
        Request $request,
        ParticipantRepository $participantRepository,
        ParticipantService $participantService,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        // Vérifier que l'utilisateur est admin
        $currentUser = $this->getUser();
        if (!$currentUser || !$currentUser->isAdministrateur()) {
            throw $this->createAccessDeniedException('Accès réservé aux administrateurs');
        }

        // Vérifier le token CSRF
        $csrfToken = $request->request->get('_csrf_token');
        if (!$csrfTokenManager->isTokenValid(new \Symfony\Component\Security\Csrf\CsrfToken('toggle_admin_' . $id, $csrfToken))) {
            $this->addFlash('error', 'Token de sécurité invalide');
            return $this->redirectToRoute('admin_users');
        }

        $user = $participantRepository->find($id);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable');
            return $this->redirectToRoute('admin_users');
        }

        // Empêcher de se retirer ses propres droits admin
        if ($user->getId() === $currentUser->getId() && $user->isAdministrateur()) {
            $this->addFlash('error', 'Vous ne pouvez pas retirer vos propres droits administrateur');
            return $this->redirectToRoute('admin_users');
        }

        $participantService->toggleAdmin($user);

        $action = $user->isAdministrateur() ? 'promu administrateur' : 'retiré des administrateurs';
        $this->addFlash('success', "L'utilisateur {$user->getPseudo()} a été {$action}");

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/users/{id}/delete', name: 'admin_delete_user', methods: ['POST'])]
    public function deleteUser(
        int $id,
        Request $request,
        ParticipantRepository $participantRepository,
        ParticipantService $participantService,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        // Vérifier que l'utilisateur est admin
        $currentUser = $this->getUser();
        if (!$currentUser || !$currentUser->isAdministrateur()) {
            throw $this->createAccessDeniedException('Accès réservé aux administrateurs');
        }

        // Vérifier le token CSRF
        $csrfToken = $request->request->get('_csrf_token');
        if (!$csrfTokenManager->isTokenValid(new \Symfony\Component\Security\Csrf\CsrfToken('delete_user_' . $id, $csrfToken))) {
            $this->addFlash('error', 'Token de sécurité invalide');
            return $this->redirectToRoute('admin_users');
        }

        $user = $participantRepository->find($id);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable');
            return $this->redirectToRoute('admin_users');
        }

        // Empêcher de se supprimer soi-même
        if ($user->getId() === $currentUser->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte');
            return $this->redirectToRoute('admin_users');
        }

        $pseudo = $user->getPseudo();
        $participantService->deleteParticipant($user);

        $this->addFlash('success', "L'utilisateur {$pseudo} a été supprimé");

        return $this->redirectToRoute('admin_users');
    }
}
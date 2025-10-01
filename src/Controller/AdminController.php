<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use App\Service\ParticipantService;
use App\Service\SiteService;
use App\Service\UserImportService;
use App\ServiceResult\Participant\CSVFileValidityResult;
use App\ServiceResult\Participant\ParticipantCSVValidityResult;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
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

        $user = $participantService->find($id);
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

        $user = $participantService->find($id);
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

    #[Route('/users/{id}/toggle-actif', name: 'admin_toggle_actif', methods: ['POST'])]
    public function toggleActif(
        int $id,
        Request $request,
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
        if (!$csrfTokenManager->isTokenValid(new \Symfony\Component\Security\Csrf\CsrfToken('toggle_actif_' . $id, $csrfToken))) {
            $this->addFlash('error', 'Token de sécurité invalide');
            return $this->redirectToRoute('admin_users');
        }

        $user = $participantService->find($id);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable');
            return $this->redirectToRoute('admin_users');
        }

        // Empêcher de se désactiver soi-même
        if ($user->getId() === $currentUser->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas désactiver votre propre compte');
            return $this->redirectToRoute('admin_users');
        }

        $participantService->toggleActif($user);

        $action = $user->isActif() ? 'réactivé' : 'désactivé';
        $this->addFlash('success', "Le compte de {$user->getPseudo()} a été {$action}");

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/admin/import', name: 'app_users_import', methods: ['POST'])]
    public function importParticipants(
        Request $request,
        UserImportService $userImportService
    ): Response {
        $file = $request->files->get('csvFile');


        if (!$file) {
            $this->addFlash('error', 'Aucun fichier sélectionné.');
            return $this->redirectToRoute('app_register');
        }

        if (($handle = fopen($file->getPathname(), 'r')) !== false) {
            $row = 0;
            //On vérifie en amont la validité du fichier csv

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $row++;

                if ($row === 1) {

                    if (($res = $userImportService->CheckCsvValidity($data)) != CSVFileValidityResult::VALID) {
                        fclose($handle);
                        $this->addFlash('error', 'Format CSV invalide : '.$res->value);
                        return $this->redirectToRoute('app_register');
                    }
                    continue;
                }

                //On vérifie la validité de chaque participant
                if(($res = $userImportService->CheckParticipantValidity($data)) != ParticipantCSVValidityResult::SUCCESS)
                {
                    fclose($handle);
                    $this->addFlash('error','Erreur ligne '.$row.' : '.$res->value);
                    return $this->redirectToRoute('app_register');
                }

            }
            rewind($handle);

            $row = 0;
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $row++;

                // On ignore l'entête (première ligne)
                if ($row === 1) {
                    continue;
                }

                $userImportService->CreateParticipantCSV($data);

            }
            fclose($handle);


            $this->addFlash('success', 'Import des participants terminé avec succès !');
        }

        return $this->redirectToRoute('admin_users');
    }



}
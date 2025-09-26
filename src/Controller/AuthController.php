<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use App\Repository\SiteRepository;
use App\Service\ParticipantService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà connecté, rediriger vers l'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('app_sortie_index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode ne sera jamais appelée car Symfony intercepte la route
        // Le logout est maintenant géré par App\Security\LogoutHandler
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        SiteRepository $siteRepository,
        ParticipantService $participantService,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        $sites = $siteRepository->findAll();
        $errors = [];

        if ($request->isMethod('POST')) {
            // Vérifier le token CSRF
            $csrfToken = $request->request->get('_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new \Symfony\Component\Security\Csrf\CsrfToken('register', $csrfToken))) {
                $errors[] = 'Token de sécurité invalide';
            } else {
                // Extraction des données
                $data = $this->extractFormData($request);
                $errors = $this->validateParticipantData($data);
                $errors = array_merge($errors, $participantService->checkUniqueFields($data['pseudo'], $data['mail']));

                // Vérifier que le site existe
                $site = null;
                if ($data['site_id']) {
                    $site = $siteRepository->find($data['site_id']);
                    if (!$site) {
                        $errors[] = 'Site invalide';
                    }
                }

                // Si pas d'erreurs, créer le participant
                if (empty($errors)) {
                    try {
                        $participantService->createParticipant(
                            $data['nom'],
                            $data['prenom'],
                            $data['pseudo'],
                            $data['mail'],
                            $data['password'],
                            $data['telephone'],
                            $site,
                            $data['photo_file']
                        );

                        $this->addFlash('success', 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.');
                        return $this->redirectToRoute('app_login');
                    } catch (\Exception $e) {
                        $errors[] = 'Erreur lors de la création du compte. Veuillez réessayer.';
                    }
                }
            }
        }

        return $this->render('auth/register.html.twig', [
            'sites' => $sites,
            'errors' => $errors,
            'formData' => $request->request->all(),
        ]);
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile(
        Request $request,
        SiteRepository $siteRepository,
        ParticipantService $participantService,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        // Vérifier que l'utilisateur est connecté
        $participant = $this->getUser();
        if (!$participant) {
            return $this->redirectToRoute('app_login');
        }

        $sites = $siteRepository->findAll();
        $errors = [];

        if ($request->isMethod('POST')) {
            // Vérifier le token CSRF
            $csrfToken = $request->request->get('_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new \Symfony\Component\Security\Csrf\CsrfToken('profile', $csrfToken))) {
                $errors[] = 'Token de sécurité invalide';
            } else {
                // Extraction et validation des données
                $data = $this->extractFormData($request);
                $errors = $this->validateParticipantData($data, true);
                $errors = array_merge($errors, $participantService->checkUniqueFields($data['pseudo'], $data['mail'], $participant->getId()));

                // Vérifier que le site existe
                $site = null;
                if ($data['site_id']) {
                    $site = $siteRepository->find($data['site_id']);
                    if (!$site) {
                        $errors[] = 'Site invalide';
                    }
                }

                // Si pas d'erreurs, mettre à jour le participant
                if (empty($errors)) {
                    try {
                        $participantService->updateParticipant(
                            $participant,
                            $data['nom'],
                            $data['prenom'],
                            $data['pseudo'],
                            $data['mail'],
                            $data['telephone'],
                            $site,
                            $data['password'] ?: null,
                            $data['photo_file'],
                            $data['delete_photo']
                        );

                        $this->addFlash('success', 'Votre profil a été mis à jour avec succès !');
                    } catch (\Exception $e) {
                        $errors[] = 'Erreur lors de la mise à jour du profil. Veuillez réessayer.';
                    }
                }
            }
        }

        return $this->render('auth/profile.html.twig', [
            'participant' => $participant,
            'sites' => $sites,
            'errors' => $errors,
            'formData' => $request->request->all(),
        ]);
    }

    private function validateParticipantData(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        if (empty($data['pseudo']) || strlen($data['pseudo']) > 30) {
            $errors[] = 'Le pseudo est obligatoire et doit faire maximum 30 caractères';
        }
        if (empty($data['nom']) || strlen($data['nom']) > 30) {
            $errors[] = 'Le nom est obligatoire et doit faire maximum 30 caractères';
        }
        if (empty($data['prenom']) || strlen($data['prenom']) > 30) {
            $errors[] = 'Le prénom est obligatoire et doit faire maximum 30 caractères';
        }
        if (empty($data['mail']) || !filter_var($data['mail'], FILTER_VALIDATE_EMAIL) || strlen($data['mail']) > 255) {
            $errors[] = 'L\'email est obligatoire et doit être valide';
        }
        if (!empty($data['telephone']) && strlen($data['telephone']) > 15) {
            $errors[] = 'Le téléphone ne doit pas dépasser 15 caractères';
        }

        // Validation du mot de passe
        if (!$isUpdate) {
            if (empty($data['password']) || strlen($data['password']) < 6) {
                $errors[] = 'Le mot de passe doit faire au moins 6 caractères';
            }
            if ($data['password'] !== $data['password_confirm']) {
                $errors[] = 'Les mots de passe ne correspondent pas';
            }
        } else {
            if (!empty($data['password'])) {
                if (strlen($data['password']) < 6) {
                    $errors[] = 'Le mot de passe doit faire au moins 6 caractères';
                }
                if ($data['password'] !== $data['password_confirm']) {
                    $errors[] = 'Les mots de passe ne correspondent pas';
                }
            }
        }

        if (empty($data['site_id'])) {
            $errors[] = 'Veuillez choisir un site';
        }

        return $errors;
    }

    private function extractFormData(Request $request): array
    {
        return [
            'pseudo' => trim($request->request->get('pseudo', '')),
            'nom' => trim($request->request->get('nom', '')),
            'prenom' => trim($request->request->get('prenom', '')),
            'mail' => trim($request->request->get('mail', '')),
            'telephone' => trim($request->request->get('telephone', '')) ?: null,
            'password' => $request->request->get('password', ''),
            'password_confirm' => $request->request->get('password_confirm', ''),
            'site_id' => $request->request->get('site_id', ''),
            'photo_file' => $request->files->get('photo_profil'),
            'delete_photo' => $request->request->has('delete_photo'),
        ];
    }

}
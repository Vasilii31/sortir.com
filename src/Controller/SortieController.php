<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SortieFilterType;
use App\Form\SortieType;
use App\Service\InscriptionService;
use App\Service\LieuService;
use App\Service\SortieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(['/','/sortie'])]
final class SortieController extends AbstractController
{
    // INDEX ___________________________________________________________________________

    #[Route('/', name: 'app_sortie_index', methods: ['GET'])]
    public function index(Request $request, SortieService $sortieService): Response
    {
        $user = $this->getUser();

        // Formulaire en GET
        $form = $this->createForm(SortieFilterType::class, null, [
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
            $sortiesWithSub = $sortieService->findFilteredSorties($criteria, $user);
        } else {
            $sortiesWithSub = $sortieService->findAllWithSubscribed($user);
        }

        return $this->render('sortie/index.html.twig', [
            'sortiesWithSub' => $sortiesWithSub,
            'form' => $form->createView(),
        ]);
    }


    // NEW ___________________________________________________________________________

    #[Route('/sortie/new', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, LieuService $lieuService, SortieService $sortieService, InscriptionService $inscriptionService): Response
    {
        $sortie = new Sortie();
        $lieux = $lieuService->getAllLieux();

        $sessionData = $request->getSession()->get('sortie_data');
        if ($sessionData) {
            $sortie = $sessionData;
            $request->getSession()->remove('sortie_data');
        }

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        $user = $this->getUser();
        if (!$user instanceof Participant) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour créer une sortie.');
        }

        $sortie->setOrganisateur($user);
        $inscriptionService->registerParticipant($sortie, $user);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateError = $sortieService->validateDates($sortie);
            if ($dateError) {
                $this->addFlash('error', $dateError);
                $request->getSession()->set('sortie_data', $form->getData());
                return $this->redirectToRoute('app_sortie_new');
            }

            $bouton = $form->get('enregistrer')->isClicked() ? 'enregistrer' : 'publier';
            $sortieService->setEtatBasedOnButton($sortie, $bouton);

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Votre sortie a été ' . ($bouton === 'enregistrer' ? 'créée.' : 'publiée.'));

            return $this->redirectToRoute('app_sortie_index');
        }

        return $this->render('sortie/new.html.twig', [
            'form' => $form->createView(),
            'lieux' => $lieux,
        ]);
    }

    // SHOW ___________________________________________________________________________

    #[Route('/{id}', name: 'app_sortie_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Sortie $sortie, SortieService $sortieService): Response
    {
        $sortieFull = $sortieService->getSortieWithParticipants($sortie->getId());

        if(!$sortie)
        {
            throw $this->createNotFoundException("Sortie non trouvée");
        }


        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortieFull,
        ]);
    }

    // EDIT ___________________________________________________________________________

    #[Route('/sortie/{id}/edit', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager, SortieService $sortieService): Response
    {
        $user = $this->getUser();

        if ($sortie->getOrganisateur() !== $user) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier cette sortie.');
            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        // Récupérer les données de session si existantes (en cas d'erreur précédente)
        $sessionData = $request->getSession()->get('sortie_data');
        if ($sessionData) {
            $sortie = $sessionData;
            $request->getSession()->remove('sortie_data');
        }

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification des dates via le service
            $dateError = $sortieService->validateDates($sortie);

            if ($dateError) {
                $this->addFlash('error', $dateError);
                // Conserver les données du formulaire pour le redirect
                $request->getSession()->set('sortie_data', $form->getData());
                return $this->redirectToRoute('app_sortie_edit', ['id' => $sortie->getId()]);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Sortie mise à jour avec succès.');
            return $this->redirectToRoute('app_sortie_index');
        }

        return $this->render('sortie/edit.html.twig', [
            'form' => $form->createView(),
            'sortie' => $sortie,
        ]);
    }

        // DELETE ___________________________________________________________________________

        #[Route('/sortie/{id}/delete', name: 'app_sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($sortie->getOrganisateur() !== $user) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer cette sortie.');
            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete' . $sortie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'La sortie a été supprimée.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
    }

    // ANNULER ___________________________________________________________________

    #[Route('/sortie/{id}/annuler', name: 'app_sortie_annuler', methods: ['POST'])]
    public function annuler(Sortie $sortie, EntityManagerInterface $em): Response
    {
        $etat = $sortie->getEtat()->getLibelle();
        $etatsNonAnnulables = ["Activité en cours", "Passée", "Historisée"];

        if (!in_array($etat, $etatsNonAnnulables, true)) {
            $etatAnnulee = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'Annulée']);
            if ($etatAnnulee) {
                $sortie->setEtat($etatAnnulee);
                $em->flush();
                $this->addFlash('success', 'La sortie a été annulée.');
            }
        } else {
            $this->addFlash('error', 'Cette sortie ne peut pas être annulée.');
        }

        return $this->redirectToRoute('app_sortie_index');
    }

    // PUBLIER ___________________________________________________________________

    #[Route('/sortie/{id}/publier', name: 'app_sortie_publier', methods: ['POST'])]
    public function publier(Sortie $sortie, EntityManagerInterface $em): Response
    {
        $etatOuvert = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'Ouverte']);
        if ($etatOuvert) {
            $sortie->setEtat($etatOuvert);
            $em->flush();
            $this->addFlash('success', 'La sortie a été publiée.');
        } else {
            $this->addFlash('error', 'Impossible de publier la sortie.');
        }

        return $this->redirectToRoute('app_sortie_index');
    }

    // INSCRIRE ___________________________________________________________________

    #[Route('/sortie/{id}/inscrire', name: 'app_sortie_inscrire', methods: ['POST'])]
    public function inscrire(Sortie $sortie, InscriptionService $inscriptionService): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Participant) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        try {
            $inscriptionService->registerParticipant($sortie, $user);
            $this->addFlash('success', 'Vous êtes maintenant inscrit à cette sortie.');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_sortie_index');
    }

    // DESISTER ___________________________________________________________________

    #[Route('/sortie/{id}/desister', name: 'app_sortie_desister', methods: ['POST'])]
    public function desister(Sortie $sortie, InscriptionService $inscriptionService): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Participant) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        try {
            $inscriptionService->unregisterParticipant($sortie, $user);
            $this->addFlash('success', 'Vous vous êtes désisté de cette sortie.');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_sortie_index');
    }
}




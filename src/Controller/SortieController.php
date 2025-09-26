<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\SortieFilterType;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use App\Service\EtatService;
use App\Service\InscriptionService;
use App\Service\LieuService;
use App\Service\ParticipantService;
use App\Service\SiteService;
use App\Service\SortieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(['/', '/sortie'])]
final class SortieController extends AbstractController
{
    // INDEX ___________________________________________________________________________

    #[Route(name: 'app_sortie_index', methods: ['GET'])]
    public function index(Request $request, SortieService $sortieService): Response
    {
        $user = $this->getUser();


        $form = $this->createForm(SortieFilterType::class);
        $form->handleRequest($request);
        $sortiesWithSub = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $sortiesWithSub = $sortieService->findFilteredSorties($form->getData(), $user);
        } else {
            $sortiesWithSub = $sortieService->findAllWithSubscribed($user);
        }


        return $this->render('sortie/index.html.twig', [
            'sortiesWithSub' => $sortiesWithSub,
            'form' => $form->createView(),
        ]);
    }




    // NEW ___________________________________________________________________________
    #[Route('/new', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        LieuService $lieuService,
        SortieService $sortieService,
        InscriptionService $inscriptionService,
    ): Response {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        $lieux = $lieuService->getAllLieux();

        $user = $this->getUser();
        if (!$user instanceof Participant) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour créer une sortie.');
        }

        $sortie->setOrganisateur($user);
        $inscriptionService->registerParticipant($sortie, $user);

        if ($form->isSubmitted() && $form->isValid()) {
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
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($sortie->getOrganisateur() !== $user) {
            $this->addFlash('error', 'Vous n’êtes pas autorisé à modifier cette sortie.');
            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

    #[
        Route('/sortie/{id}', name: 'app_sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($sortie->getOrganisateur() !== $user) {
            $this->addFlash('error', 'Vous n’êtes pas autorisé à supprimer cette sortie.');
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
            $sortie->setEtat($etatAnnulee);
            $em->flush();
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
        }

        return $this->redirectToRoute('app_sortie_index');
    }

}






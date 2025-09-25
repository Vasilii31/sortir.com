<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\SortieFilterType;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use App\Service\EtatService;
use App\Service\LieuService;
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
    #[Route(name: 'app_sortie_index', methods: ['GET'])]
    public function index(Request $request, SortieService $sortieService): Response
    {

        $form = $this->createForm(SortieFilterType::class);
        $form->handleRequest($request);
        $sortiesWithSub = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $sortiesWithSub = $sortieService->findFilteredSorties($form->getData());
        } else {
            $sortiesWithSub = $sortieService->findAllWithSubscribed();
        }

        return $this->render('sortie/index.html.twig', [
            'sortiesWithSub' => $sortiesWithSub,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(
        Request                $request,
        EntityManagerInterface $entityManager,
        LieuService            $lieuService,
        EtatService            $etatService,
    ): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        $lieux = $lieuService->getAllLieux();
        $etats = $etatService->getAllEtats();

        // Créer un tableau associatif id => objet Etat
        $etatsParId = [];
        foreach ($etats as $etat) {
            $etatsParId[$etat->getId()] = $etat;
        }


        if ($form->isSubmitted() && $form->isValid()) {

            // Gestion des boutons
            if ($form->get('enregistrer')->isClicked()) {
                $sortie->setEtat($etatsParId[1] ?? null);  // Créée - id = 1
            } elseif ($form->get('publier')->isClicked()) {
                $sortie->setEtat($etatsParId[2] ?? null); // Ouverte - id = 2
            }

            // TODO Implémenter les data relatifs au user connecté

            $user = $entityManager->getRepository(Participant::class)->find(1);

            $sortie->setOrganisateur($user);


            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_sortie_index');
        }

        return $this->render('sortie/new.html.twig', [
            'form' => $form->createView(),
            'lieux' => $lieux,
        ]);
    }

    #[Route('/sortie/{id}', name: 'app_sortie_show', methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/sortie/{id}/edit', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        // 👉 On ne passe plus 'ville_selected' ni 'lieu_selected'
        $form = $this->createForm(SortieType::class, $sortie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sortie_index');
        }

        return $this->render('sortie/edit.html.twig', [
            'form' => $form->createView(),
            'sortie' => $sortie,
        ]);
    }


    #[Route('/sortie/{id}', name: 'app_sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $sortie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
    }
}




<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SiteType;
use App\Service\SiteService;
use App\ServiceResult\Site\DeleteSiteResult;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/site')]
final class SiteController extends AbstractController
{
    public function __construct(private readonly SiteService $siteService) {}
    #[Route(name: 'app_site_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('site/index.html.twig', [
            'sites' => $this->siteService->getAllSites(),
        ]);
    }

//    #[Route('/new', name: 'app_site_new', methods: ['GET', 'POST'])]
//    public function new(Request $request, EntityManagerInterface $entityManager): Response
//    {
//        $site = new Site();
//        $form = $this->createForm(SiteType::class, $site);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $entityManager->persist($site);
//            $entityManager->flush();
//
//            return $this->redirectToRoute('app_site_index', [], Response::HTTP_SEE_OTHER);
//        }
//
//        return $this->render('site/new.html.twig', [
//            'site' => $site,
//            'form' => $form,
//        ]);
//    }

    #[Route('/new', name: 'app_site_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $site = new Site();
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // délégation à la couche métier
           $this->siteService->createSite($site->getNomSite());

            return $this->redirectToRoute('app_site_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('site/new.html.twig', [
            'site' => $site,
            'form' => $form,
        ]);
    }



//    #[Route('/{id}/edit', name: 'app_site_edit', methods: ['GET', 'POST'])]
//    public function edit(Request $request, Site $site, EntityManagerInterface $entityManager): Response
//    {
//        $form = $this->createForm(SiteType::class, $site);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $entityManager->flush();
//
//            return $this->redirectToRoute('app_site_index', [], Response::HTTP_SEE_OTHER);
//        }
//
//        return $this->render('site/edit.html.twig', [
//            'site' => $site,
//            'form' => $form,
//        ]);
//    }
//    #[Route('/{id}/edit', name: 'app_site_edit', methods: ['GET', 'POST'])]
//    public function edit(Request $request, Site $site, SiteService $siteService): Response
//    {
//        $form = $this->createForm(SiteType::class, $site);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $newNom = $form->get('nom_site')->getData();
//            $result = $siteService->updateSite($site, $newNom);
//
//            match ($result) {
//                UpdateSiteResult::SUCCESS =>
//                $this->addFlash('success', 'Site modifié avec succès.'),
//                UpdateSiteResult::NAME_ALREADY_USED =>
//                $this->addFlash('error', 'Un site avec ce nom existe déjà.'),
//            };
//
//            if ($result === UpdateSiteResult::SUCCESS) {
//                return $this->redirectToRoute('app_site_index', [], Response::HTTP_SEE_OTHER);
//            }
//        }
//
//        return $this->render('site/edit.html.twig', [
//            'site' => $site,
//            'form' => $form,
//        ]);
//    }

    #[Route('/{id}/edit', name: 'app_site_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Site $site): Response
    {
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Symfony a déjà validé l'unicité grâce à @UniqueEntity
            $this->siteService->updateSite($site, $form->get('nom_site')->getData());

            $this->addFlash('success', 'Site modifié avec succès.');
            return $this->redirectToRoute('app_site_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('site/edit.html.twig', [
            'site' => $site,
            'form' => $form->createView(),
        ]);
    }



    #[Route('/{id}', name: 'app_site_delete', methods: ['POST'])]
    public function delete(Request $request, Site $site): Response
    {
        if ($this->isCsrfTokenValid('delete'.$site->getId(), $request->getPayload()->getString('_token'))) {
            $result = $this->siteService->deleteSite($site);

            match ($result) {
                DeleteSiteResult::SUCCESS =>
                $this->addFlash('success', 'Site supprimé avec succès.'),
                DeleteSiteResult::SITE_IN_USE =>
                $this->addFlash('error', 'Impossible de supprimer ce site car il est utilisé.'),
            };
        }

        return $this->redirectToRoute('app_site_index', [], Response::HTTP_SEE_OTHER);
    }


//    #[Route('/{id}', name: 'app_site_delete', methods: ['POST'])]
//    public function delete(Request $request, Site $site, EntityManagerInterface $entityManager): Response
//    {
//        if ($this->isCsrfTokenValid('delete'.$site->getId(), $request->getPayload()->getString('_token'))) {
//            $entityManager->remove($site);
//            $entityManager->flush();
//        }
//
//        return $this->redirectToRoute('app_site_index', [], Response::HTTP_SEE_OTHER);
//    }
}

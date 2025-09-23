<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Service\VilleService;
use App\ServiceResult\Ville\DeleteVilleResult;
use App\ServiceResult\Ville\UpdateVilleResult;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ville')]
final class VilleController extends AbstractController
{
    public function __construct(private readonly VilleService $villeService)
    {

    }

    #[Route(name: 'app_ville_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('ville/index.html.twig', [
            'villes' => $this->villeService->getAllVilles(),
        ]);
    }

    #[Route('/new', name: 'app_ville_new', methods: ['GET', 'POST'])]
    public function new(Request $request, VilleService $villeService): Response
    {
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           $villeService->createVille($ville->getNomVille(),$ville->getCodePostal());

            return $this->redirectToRoute('app_ville_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ville/new.html.twig', [
            'ville' => $ville,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ville_show', methods: ['GET'])]
    public function show(Ville $ville): Response
    {
        return $this->render('ville/show.html.twig', [
            'ville' => $ville,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ville_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ville $ville): Response
    {
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $res = $this->villeService->UpdateVille($ville, $form->get('nom_ville')->getData(), $form->get('code_postal')->getData());
            if ($res == UpdateVilleResult::SUCCESS)
            {
                $this->addFlash('success', 'Ville modifiée avec succès.');
                return $this->redirectToRoute('app_ville_index', [], Response::HTTP_SEE_OTHER);
            }

        }

        return $this->render('ville/edit.html.twig', [
            'ville' => $ville,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ville_delete', methods: ['POST'])]
    public function delete(Request $request, Ville $ville): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ville->getId(), $request->getPayload()->getString('_token'))) {
            $result = $this->villeService->deleteVIlle($ville);


            match ($result){
                DeleteVilleResult::SUCCESS =>
                $this->addFlash('success', 'Ville supprimée avec succès.'),
                DeleteVilleResult::VILLE_IN_USE =>
                $this->addFlash('error', 'Impossible de supprimer cette ville car elle est utilisée dans un lieu.'),
            };
        }
        return $this->redirectToRoute('app_ville_index', [], Response::HTTP_SEE_OTHER);
    }
}

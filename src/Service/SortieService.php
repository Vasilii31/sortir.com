<?php

namespace App\Service;

use App\Dto\ParticipantDTO;
use App\Dto\SortieFullDTO;
use App\Entity\Sortie;
use App\Repository\SortieRepository;

class SortieService
{
    private  readonly SortieRepository $sortieRepository;

    public function __construct(SortieRepository $sortieRepository)
    {
        $this->sortieRepository = $sortieRepository;
    }

    public function findFilteredSorties(array $searchCriteria): array
    {
        return $this->sortieRepository->FindByFilter($searchCriteria);
    }

    public function getSortieWithParticipants(int $id): ?SortieFullDTO
    {
        $sortie = $this->sortieRepository->findWithParticipants($id);
        if(!$sortie){return null;}
        $dto = new SortieFullDTO();
        $dto->id = $sortie->getId();
        $dto->nom = $sortie->getNom();
        $dto->dateDebut = $sortie->getDatedebut();
        $dto->datecloture = $sortie->getDatecloture();
        $dto->description = $sortie->getDescriptioninfos();
        $dto->etat = $sortie->getEtat()->getLibelle();
        $dto->duree = $sortie->getDuree();
        $dto->organisateur = $sortie->getOrganisateur()->getPseudo();
        $dto->lieu = $sortie->getLieu();
        $dto->ville = $sortie->getLieu()->getVille()->getNomVille();


        foreach ($sortie->getInscriptions() as $inscription) {
            $pDto = new ParticipantDTO();
            $pDto->id = $inscription->getParticipant()->getId();
            $pDto->pseudo = $inscription->getParticipant()->getPseudo();
            $pDto->nom = $inscription->getParticipant()->getNom();
            $pDto->prenom = $inscription->getParticipant()->getPrenom();

            $pDto->urlPhoto = $inscription->getParticipant()->getPhotoProfil() ?? "";

            $dto->participants[] = $pDto;
        }

        return $dto;
    }
    public function findAll(): array
    {
        return $this->sortieRepository->findAll();
    }
    public function findAllWithSubscribed(): array
    {
        return $this->sortieRepository->findAllWithSubscribed();
    }
}
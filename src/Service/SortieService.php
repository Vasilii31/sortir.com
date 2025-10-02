<?php

namespace App\Service;

use App\Dto\ParticipantDTO;
use App\Dto\SortieFullDTO;
use App\Entity\Sortie;
use App\Dto\SortieInscritsDTO;
use App\Entity\Inscription;
use App\Entity\Participant;
use App\Repository\SortieRepository;

class SortieService
{
    private readonly SortieRepository $sortieRepository;

    public function __construct(SortieRepository $sortieRepository, EtatService $etatService)
    {
        $this->sortieRepository = $sortieRepository;
        $this->etatService = $etatService;

    }

    /**
     * Assigne l'état d'une sortie selon le bouton cliqué.
     */
    public function setEtatBasedOnButton(Sortie $sortie, string $bouton): void
    {
        $etats = $this->etatService->getAllEtats();
        $etatsParLibelle = [];
        foreach ($etats as $etat) {
            $etatsParLibelle[$etat->getLibelle()] = $etat;
        }

        if ($bouton === 'enregistrer') {
            $sortie->setEtat($etatsParLibelle['Créée']);
        } elseif ($bouton === 'publier') {
            $sortie->setEtat($etatsParLibelle['Ouverte']);
        }
    }

    /**
     * Retourne les sorties filtrées avec le nombre d'inscrits et la participation de l'utilisateur.
     */
    public function findFilteredSorties(array $criteria, Participant $user = null): array
    {
        $rawResults = $this->sortieRepository->findByFilter($criteria, $user);

        return array_map(function($row) use ($user) {
            $sortie = $row[0];
            $nbInscrits = (int)$row['nbInscrits'];

            $isParticipating = false;
            if ($user) {
                foreach ($sortie->getInscriptions() as $inscription) {
                    if ($inscription->getParticipant() === $user) {
                        $isParticipating = true;
                        break;
                    }
                }
            }

            return new SortieInscritsDTO($sortie, $nbInscrits, $isParticipating);
        }, $rawResults);
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
        $dto->nbInscriptionMax = $sortie->getNbInscriptionsMax();
        $dto->description = $sortie->getDescriptioninfos();
        $dto->etat = $sortie->getEtat()->getLibelle();
        $dto->duree = $sortie->getDuree();
        $dto->organisateur = new ParticipantDTO();
        $dto->organisateur->id = $sortie->getOrganisateur()->getId();
        $dto->organisateur->nom = $sortie->getOrganisateur()->getNom();
        $dto->organisateur->prenom = $sortie->getOrganisateur()->getPrenom();
        $dto->organisateur->pseudo = $sortie->getOrganisateur()->getPseudo();
        $dto->isPrivate = $sortie->isPrivate();
        $dto->lieu = $sortie->getLieu();
        $dto->ville = $sortie->getLieu()->getVille()->getNomVille();

        $dto->participantsPrives = $sortie->getParticipantsPrives();
//        for($i = 0; $i < 20; $i++)
//        {


            foreach ($sortie->getInscriptions() as $inscription) {
                $pDto = new ParticipantDTO();
                $pDto->id = $inscription->getParticipant()->getId();
                $pDto->pseudo = $inscription->getParticipant()->getPseudo();
                $pDto->nom = $inscription->getParticipant()->getNom();
                $pDto->prenom = $inscription->getParticipant()->getPrenom();

                $pDto->urlPhoto = $inscription->getParticipant()->getPhotoProfil() ?? "";

                $dto->participants[] = $pDto;
            }
        //}

        return $dto;
    }
    public function findAll(): array
    {
        return $this->sortieRepository->findAll();
    }



    public function findAllWithSubscribed(?Participant $user = null): array
    {
        $rawResults = $this->sortieRepository->findAllWithSubscribed($user);

        return array_map(function($row) use ($user) {
            $sortie = $row[0];
            $nbInscrits = (int)$row['nbInscrits'];

            $isParticipating = false;
            if ($user) {
                foreach ($sortie->getInscriptions() as $inscription) {
                    if ($inscription->getParticipant() === $user) {
                        $isParticipating = true;
                        break;
                    }
                }
            }

            return new SortieInscritsDTO($sortie, $nbInscrits, $isParticipating);
        }, $rawResults);
    }


    public function validateDates(Sortie $sortie): ?string
    {
        $now = new \DateTime();
        $datedebut = $sortie->getDatedebut();
        $datecloture = $sortie->getDatecloture();

        if ($datedebut < $now) {
            return 'La date de début ne peut pas être antérieure à aujourd’hui.';
        }
        if ($datecloture < $now) {
            return 'La date de clôture ne peut pas être  antérieure à aujourd’hui.';
        }
        if ($datecloture > $datedebut) {
            return 'La date de clôture ne peut pas être  postérieure à la date de début.';
        }

        return null;
    }




    public function findByUserSite(Participant $participant)
    {
        return $this->sortieRepository->findWithSubscribedBySite($participant);
    }


}
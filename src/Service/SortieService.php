<?php

namespace App\Service;

use App\Dto\SortieInscritsDTO;
use App\Entity\Inscription;
use App\Entity\Participant;
use App\Entity\Sortie;
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
    public function findFilteredSorties(array $criteria, ?Participant $user = null): array
    {
        $rawResults = $this->sortieRepository->findByFilter($criteria);

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

    public function findAll(): array
    {
        return $this->sortieRepository->findAll();
    }

    public function findAllWithSubscribed(?Participant $user = null): array
    {
        $rawResults = $this->sortieRepository->findAllWithSubscribed();

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
}
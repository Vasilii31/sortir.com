<?php

namespace App\Service;

use App\Dto\SortieInscritsDTO;
use App\Entity\Participant;
use App\Repository\SortieRepository;

class SortieService
{
    private readonly SortieRepository $sortieRepository;

    public function __construct(SortieRepository $sortieRepository)
    {
        $this->sortieRepository = $sortieRepository;
    }

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
}
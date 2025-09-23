<?php

namespace App\Service;

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

    public function findAll(): array
    {
        return $this->sortieRepository->findAll();
    }
    public function findAllWithSubscribed(): array
    {
        return $this->sortieRepository->findAllWithSubscribed();
    }
}
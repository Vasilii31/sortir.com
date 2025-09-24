<?php

namespace App\Service;

use App\Repository\EtatRepository;

class EtatService
{
    public function __construct(
        private readonly EtatRepository  $etatRepository,
    ) {}

    /**
     * Retourne tous les Etats
     */
    public function getAllEtats(): array
    {
        return $this->etatRepository->findAll();
    }

}
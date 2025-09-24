<?php

namespace App\Service;

use App\Repository\VilleRepository;

class VilleService
{
    public function __construct(
        private readonly VilleRepository  $villeRepository,
    ) {}

    /**
     * Retourne tous les lieux
     */
    public function getAllVilles(): array
    {
        return $this->villeRepository->findAll();
    }

}
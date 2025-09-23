<?php

namespace App\Service;

use App\Repository\LieuRepository;

class LieuService
{
    public function __construct(
        private readonly LieuRepository  $lieuRepository,
    ) {}

    /**
     * Retourne tous les lieux
     */
    public function getAllLieux(): array
    {
        return $this->lieuRepository->findAll();
    }

}
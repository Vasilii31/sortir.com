<?php

namespace App\Dto;

use App\Entity\Sortie;

class SortieInscritsDTO
{
    public function __construct(
        public readonly Sortie $sortie,
        public readonly int $nbInscrits
    )
    {
    }


}
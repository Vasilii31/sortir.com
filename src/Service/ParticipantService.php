<?php

namespace App\Service;

use App\Repository\ParticipantRepository;

class ParticipantService
{
    public function __construct(
        private readonly ParticipantRepository  $participantRepository,
    ) {}

    /**
     * Retourne tous les Etats
     */
    public function getAllParticipants(): array
    {
        return $this->participantRepository->findAll();
    }

}
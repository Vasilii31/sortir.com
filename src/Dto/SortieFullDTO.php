<?php

namespace App\Dto;

use App\Entity\Lieu;
use Doctrine\Common\Collections\Collection;

class SortieFullDTO
{
    public int $id;
    public string $nom;
    public \DateTimeInterface $dateDebut;
    public \DateTimeInterface $datecloture;
    public int $nbInscriptionMax;
    public int $duree;
    public string $etat;
    public ParticipantDTO $organisateur;
    public bool $isPrivate;

    public ?string $description = null;
    public ?Lieu $lieu;
    public string $ville;

    /** @var ParticipantDto[] */
    public array $participants = [];

    public Collection $participantsPrives;
}
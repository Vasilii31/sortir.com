<?php

namespace App\Service;

use App\Entity\Inscription;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Repository\InscriptionRepository;
use App\ServiceResult\Inscription\CreateInscriptionResult;

class InscriptionService
{
    public function __construct(private readonly InscriptionRepository $inscriptionRepository) {}
    public function inscrireParticipant(Sortie $sortie, Participant $participant): CreateInscriptionResult
    {
        // Vérifier si déjà inscrit
        if ($this->inscriptionRepository->isParticipantInscrit($sortie, $participant)) {
            return CreateInscriptionResult::ALREADY_SUBSCRIBED;
        }

        $inscription = new Inscription();
        $inscription->setSortie($sortie);
        $inscription->setParticipant($participant);
        $inscription->setDateInscription(new \DateTime());

        $this->inscriptionRepository->save($inscription);

        return CreateInscriptionResult::SUCCESS;
    }
}
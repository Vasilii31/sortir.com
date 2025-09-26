<?php

namespace App\Service;


use App\Entity\Inscription;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;

class InscriptionService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function registerParticipant(Sortie $sortie, Participant $participant): void
    {

        $isOrganisateur = $sortie->getOrganisateur() === $participant;

        // Vérification etat sortie
        if (!$isOrganisateur && $sortie->getEtat()->getLibelle() !== 'Ouverte') {
            throw new \DomainException('Impossible de s’inscrire : la sortie n’est pas ouverte.');
        }

        // Vérifie si le participant est déjà inscrit
        foreach ($sortie->getInscriptions() as $inscription) {
            if ($inscription->getParticipant() === $participant) {
                return; // déjà inscrit, ne rien faire
            }
        }

        $inscription = new Inscription();
        $inscription->setSortie($sortie);
        $inscription->setDateInscription(new \DateTime());
        $inscription->setParticipant($participant);

        $this->entityManager->persist($inscription);
    }


    public function unregisterParticipant(Sortie $sortie, Participant $participant): void
    {
        foreach ($sortie->getInscriptions() as $inscription) {
            if ($inscription->getParticipant() === $participant) {
                $this->entityManager->remove($inscription);
                return;
            }
        }
    }
}

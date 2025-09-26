<?php

namespace App\Service;


use App\Entity\Etat;
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

        // Vérification état sortie
        if (!$isOrganisateur && $sortie->getEtat()->getLibelle() !== 'Ouverte') {
            throw new \DomainException('Impossible de s’inscrire : la sortie n’est pas ouverte.');
        }

        foreach ($sortie->getInscriptions() as $inscription) {
            if ($inscription->getParticipant() === $participant) {
                return; // déjà inscrit
            }
        }


        $inscription = new Inscription();
        $inscription->setSortie($sortie);
        $inscription->setDateInscription(new \DateTime());
        $inscription->setParticipant($participant);

        $this->entityManager->persist($inscription);

        $nbInscrits = count($sortie->getInscriptions()) + 1;
        if ($nbInscrits >= $sortie->getNbInscriptionsMax()) {
            $etatCloturee = $this->entityManager
                ->getRepository(Etat::class)
                ->findOneBy(['libelle' => 'Clôturée']);
            $sortie->setEtat($etatCloturee);
        }
    }

    public function unregisterParticipant(Sortie $sortie, Participant $participant): void
    {
        foreach ($sortie->getInscriptions() as $inscription) {
            if ($inscription->getParticipant() === $participant) {
                $this->entityManager->remove($inscription);

                // Vérifie si la sortie peut repasser à "Ouverte"
                $nbInscrits = count($sortie->getInscriptions()) - 1;
                $etatOuverte = $this->entityManager
                    ->getRepository(Etat::class)
                    ->findOneBy(['libelle' => 'Ouverte']);

                $now = new \DateTime();
                if ($nbInscrits < $sortie->getNbInscriptionsMax() && $sortie->getDateCloture() > $now) {
                    $sortie->setEtat($etatOuverte);
                }

                return;
            }
        }
    }
}

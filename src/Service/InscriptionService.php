<?php

namespace App\Service;


use App\Entity\Inscription;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Repository\EtatRepository;
use App\Repository\InscriptionRepository;
use App\Repository\SortieRepository;

class InscriptionService
{
    public function __construct(
        private readonly InscriptionRepository $inscriptionRepository,
        private readonly EtatRepository $etatRepository,
        private readonly SortieRepository $sortieRepository,

    ) {}

    public function registerParticipant(Sortie $sortie, Participant $participant): void
    {
        $isOrganisateur = $sortie->getOrganisateur() === $participant;

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

        $this->inscriptionRepository->save($inscription);

        $nbInscrits = count($sortie->getInscriptions()) + 1;
        if ($nbInscrits >= $sortie->getNbInscriptionsMax()) {
            $etatCloturee = $this->etatRepository->findOneBy(['libelle' => 'Clôturée']);
            $sortie->setEtat($etatCloturee);


            $this->inscriptionRepository->save($sortie);
        }
    }

    public function unregisterParticipant(Sortie $sortie, Participant $participant): void
    {
        foreach ($sortie->getInscriptions() as $inscription) {
            if ($inscription->getParticipant() === $participant) {

                $this->inscriptionRepository->remove($inscription);

                $nbInscrits = count($sortie->getInscriptions()) - 1;
                $etatOuverte = $this->etatRepository->findOneBy(['libelle' => 'Ouverte']);

                $now = new \DateTime();
                if ($nbInscrits < $sortie->getNbInscriptionsMax() && $sortie->getDateCloture() > $now) {
                    $sortie->setEtat($etatOuverte);

                    $this->sortieRepository->save($sortie);
                }

                return;
            }
        }
    }
}

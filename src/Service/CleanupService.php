<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class CleanupService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Nettoie les inscriptions des participants désactivés aux sorties terminées
     */
    public function cleanupFinishedSortiesForInactiveParticipants(): int
    {
        $now = new \DateTime();

        // Supprimer les inscriptions des participants inactifs aux sorties terminées
        $query = $this->entityManager->createQuery(
            'DELETE FROM App\Entity\Inscription i
             WHERE i.participant IN (
                 SELECT p.id FROM App\Entity\Participant p WHERE p.actif = false
             )
             AND i.Sortie IN (
                 SELECT s.id FROM App\Entity\Sortie s
                 WHERE (s.duree IS NOT NULL AND DATE_ADD(s.datedebut, s.duree, \'MINUTE\') < :now)
                    OR (s.duree IS NULL AND s.datedebut < :now)
             )'
        );
        $query->setParameter('now', $now);

        $deletedInscriptions = $query->execute();

        // Supprimer les sorties terminées organisées par des participants inactifs
        $query = $this->entityManager->createQuery(
            'DELETE FROM App\Entity\Sortie s
             WHERE s.organisateur IN (
                 SELECT p.id FROM App\Entity\Participant p WHERE p.actif = false
             )
             AND ((s.duree IS NOT NULL AND DATE_ADD(s.datedebut, s.duree, \'MINUTE\') < :now)
                  OR (s.duree IS NULL AND s.datedebut < :now))'
        );
        $query->setParameter('now', $now);

        $deletedSorties = $query->execute();

        return $deletedInscriptions + $deletedSorties;
    }

    /**
     * Marque comme annulées les sorties en cours dont l'organisateur est inactif
     */
    public function cancelOngoingSortiesForInactiveOrganizers(): int
    {
        $now = new \DateTime();

        $etatAnnule = $this->entityManager->getRepository('App\Entity\Etat')->findOneBy(['libelle' => 'Annulée']);
        if (!$etatAnnule) {
            return 0;
        }

        $query = $this->entityManager->createQuery(
            'UPDATE App\Entity\Sortie s
             SET s.etat = :etatAnnule
             WHERE s.organisateur IN (
                 SELECT p.id FROM App\Entity\Participant p WHERE p.actif = false
             )
             AND s.datedebut <= :now
             AND ((s.duree IS NOT NULL AND DATE_ADD(s.datedebut, s.duree, \'MINUTE\') > :now) OR s.duree IS NULL)
             AND s.etat != :etatAnnule'
        );
        $query->setParameter('etatAnnule', $etatAnnule);
        $query->setParameter('now', $now);

        return $query->execute();
    }
}
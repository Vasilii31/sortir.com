<?php

namespace App\Scheduler\Task;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

#[AsPeriodicTask(frequency: '5 seconds')]
class UpdateSortiesEtatTask
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(): void
    {
        $conn = $this->entityManager->getConnection();
        $conn->beginTransaction();

        try {
            $conn->executeStatement("
                UPDATE sortie s
                INNER JOIN etat e ON s.etat_id = e.id
                SET s.etat_id = (
                    SELECT id 
                    FROM etat 
                    WHERE libelle = 'Clôturée'
                )
                WHERE e.libelle = 'Ouverte'
                  AND s.datecloture < NOW()
            ");

            $conn->executeStatement("
                UPDATE sortie s
                INNER JOIN etat e ON s.etat_id = e.id
                SET s.etat_id = (
                    SELECT id 
                    FROM etat 
                    WHERE libelle = 'Passée'
                )
                WHERE e.libelle = 'Activité en cours'
                  AND DATE_ADD(s.datedebut, INTERVAL s.duree MINUTE) < NOW()
            ");

            $conn->executeStatement("
                UPDATE sortie s
                INNER JOIN etat e ON s.etat_id = e.id
                SET s.etat_id = (
                    SELECT id 
                    FROM etat 
                    WHERE libelle = 'Historisée'
                )
                WHERE e.libelle IN ('Passée','Annulée')
                  AND s.datedebut < NOW() - INTERVAL 1 MONTH
            ");

            $conn->commit();

            dump('Sorties updated with success at' . (new \DateTime())->format('Y-m-d H:i:s'));
        } catch (\Exception $e) {
            $conn->rollBack();
            dump('Error: ' . $e->getMessage());
        }
    }
}

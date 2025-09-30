<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateEtatSortiesCommand extends Command
{
    protected static $defaultName = 'app:update-sorties-etat';

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Met à jour automatiquement les états des sorties toutes les minutes.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $conn = $this->em->getConnection();

        try {
            $conn->beginTransaction();

            // --- 1. Passer en "Clôturée" si date_cloture dépassée et état = Ouverte ---
            $sql1 = "
            UPDATE sortie s
            INNER JOIN etat e ON s.etat_id = e.id
            SET s.etat_id = (SELECT id FROM etat WHERE libelle = 'Clôturée')
            WHERE e.libelle = 'Ouverte' AND s.datecloture < NOW()
        ";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->executeStatement();
            $output->writeln("Sorties ouvertes cloturées.");

            // --- 2. Passer en "Passée" si activité terminée (etat = Activité en cours) ---
            $sql2 = "
            UPDATE sortie s
            INNER JOIN etat e ON s.etat_id = e.id
            SET s.etat_id = (SELECT id FROM etat WHERE libelle = 'Passée')
            WHERE e.libelle = 'Activité en cours' 
              AND DATE_ADD(s.datedebut, INTERVAL s.duree MINUTE) < NOW()
        ";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->executeStatement();
            $output->writeln("Sorties en cours passées.");

            // --- 3. Historiser après 1 mois (état = Passée ou Annulée) ---
            $sql3 = "
            UPDATE sortie s
            INNER JOIN etat e ON s.etat_id = e.id
            SET s.etat_id = (SELECT id FROM etat WHERE libelle = 'Historisée')
            WHERE e.libelle IN ('Passée','Annulée') 
              AND s.datedebut < NOW() - INTERVAL 1 MONTH
        ";
            $stmt3 = $conn->prepare($sql3);
            $stmt3->executeStatement();
            $output->writeln("Sorties historisées.");

            $conn->commit();
            $output->writeln("Mise à jour des états terminée.");

        } catch (\Exception $e) {
            $conn->rollBack();
            $output->writeln("Erreur lors de la mise à jour : " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }


}
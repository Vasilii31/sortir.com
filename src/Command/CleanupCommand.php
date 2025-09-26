<?php

namespace App\Command;

use App\Service\CleanupService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:cleanup-inactive-participants',
    description: 'Nettoie les inscriptions et sorties des participants inactifs'
)]
class CleanupCommand extends Command
{
    public function __construct(
        private readonly CleanupService $cleanupService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Début du nettoyage des participants inactifs...');

        // Nettoyer les sorties terminées
        $cleanedItems = $this->cleanupService->cleanupFinishedSortiesForInactiveParticipants();
        $io->success("$cleanedItems inscriptions/sorties terminées nettoyées");

        // Annuler les sorties en cours
        $cancelledSorties = $this->cleanupService->cancelOngoingSortiesForInactiveOrganizers();
        $io->success("$cancelledSorties sorties en cours annulées");

        $io->success('Nettoyage terminé avec succès !');

        return Command::SUCCESS;
    }
}
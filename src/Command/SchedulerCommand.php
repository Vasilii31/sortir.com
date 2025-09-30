<?php

namespace App\Command;


use App\Service\SortiesEtatScheduler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchedulerCommand extends Command
{
    protected static $defaultName = 'app:scheduler';

    private SortiesEtatScheduler $scheduler;

    public function __construct(SortiesEtatScheduler $scheduler)
    {
        parent::__construct();
        $this->scheduler = $scheduler;
    }

    protected function configure(): void
    {
        $this->setDescription('Exécute les tâches planifiées.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->scheduler->run();
        $output->writeln('Scheduler exécuté.');
        return Command::SUCCESS;
    }
}
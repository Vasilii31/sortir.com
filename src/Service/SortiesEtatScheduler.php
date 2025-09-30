<?php

namespace App\Service;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SortiesEtatScheduler
{
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function run(): void
    {

        $process = new Process([
            'php',
            $this->projectDir . '/bin/console',
            'app:update-sorties-etat',
            '--env=prod'
        ]);

        $process->run();

        file_put_contents(
            $this->projectDir . '/var/log/scheduler.log',
            '[' . date('Y-m-d H:i:s') . '] STDOUT: ' . $process->getOutput() . PHP_EOL .
            '[' . date('Y-m-d H:i:s') . '] STDERR: ' . $process->getErrorOutput() . PHP_EOL,
            FILE_APPEND
        );

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

}
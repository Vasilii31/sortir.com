#!/usr/bin/env php
<?php
// bin/setup-scheduler.php

$console = __DIR__ . '/console';
$command = "app:update-sorties-etat";

// Détecter le système
if (PHP_OS_FAMILY === 'Windows') {
    echo "Windows détecté, création d'une tâche planifiée...\n";

    $taskName = "UpdateSortiesEtat";
    $phpPath = PHP_BINARY; // chemin vers php.exe
    $cmd = sprintf(
        'schtasks /Create /SC MINUTE /MO 1 /TN "%s" /TR "%s %s" /F',
        $taskName,
        $phpPath,
        $console . ' ' . $command
    );

    echo "Commande : $cmd\n";
    exec($cmd, $output, $returnVar);
    if ($returnVar === 0) {
        echo "Tâche Windows créée avec succès !\n";
    } else {
        echo "Erreur lors de la création de la tâche.\n";
        print_r($output);
    }

} else {
    echo "Linux/macOS détecté, ajout au crontab...\n";

    $cronJob = "* * * * * " . PHP_BINARY . " " . $console . " " . $command . " >> " . __DIR__ . "/../var/log/update-sorties.log 2>&1";

    // Ajouter la tâche si elle n'existe pas déjà
    exec("crontab -l 2>/dev/null", $currentCrons);
    if (!in_array($cronJob, $currentCrons)) {
        $currentCrons[] = $cronJob;
        $cronStr = implode("\n", $currentCrons);
        $tmpFile = tempnam(sys_get_temp_dir(), 'cron');
        file_put_contents($tmpFile, $cronStr);
        exec("crontab " . $tmpFile);
        unlink($tmpFile);
        echo "Cron ajouté avec succès !\n";
    } else {
        echo "La tâche cron existe déjà.\n";
    }
}

echo "Setup terminé.\n";

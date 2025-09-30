<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20250930111111 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO etat (id, libelle) VALUES
        (1, 'Créée'),
        (2, 'Ouverte'),
        (3, 'Clôturée'),
        (4, 'Activité en cours'),
        (5, 'Passée'),
        (6, 'Annulée'),
        (7, 'Historisée')
    ON DUPLICATE KEY UPDATE libelle = VALUES(libelle)");
    }

}
<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $libelles = ['Créée', 'Ouverte', 'Clôturée', 'Activité en cours', 'Passée', 'Annulée'];

        foreach ($libelles as $key => $libelle) {
            $etat = new Etat();
            $etat->setLibelle($libelle);

            $manager->persist($etat);
            $manager->flush(); // flush maintenant pour générer l'id

            // Ajouter une référence logique
            $this->addReference('etat-' . ($key + 1), $etat);
        }
    }
}
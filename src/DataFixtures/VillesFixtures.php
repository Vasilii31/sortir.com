<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VillesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $villes = [
            ['nom' => 'Paris', 'cp' => '75000'],
            ['nom' => 'Lyon', 'cp' => '69000'],
            ['nom' => 'Marseille', 'cp' => '13000'],
            ['nom' => 'Nantes', 'cp' => '440000'],
            ['nom' => 'Toulouse', 'cp' => '31000'],
            ['nom' => 'Niort', 'cp' => '79000'],
        ];

        foreach ($villes as $key => $data) {
            $ville = new Ville();
            $ville->setNomVille($data['nom']);
            $ville->setCodePostal($data['cp']);

            $manager->persist($ville);
            $manager->flush();

            $this->addReference('ville-' . strtolower($data['nom']), $ville);
        }
    }
}

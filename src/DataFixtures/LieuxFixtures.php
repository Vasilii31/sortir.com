<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LieuxFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $lieuxData = [
            [
                'nom' => 'Stade de France',
                'rue' => 'Rue de Saint-Denis',
                'ville' => 'Paris',
                'latitude' => 48.9244,
                'longitude' => 2.3601
            ],
            [
                'nom' => 'Parc OL',
                'rue' => 'Rue des Décines',
                'ville' => 'Lyon',
                'latitude' => 45.7640,
                'longitude' => 4.9990
            ],
            [
                'nom' => 'Vélodrome',
                'rue' => 'Rue de la brancardière',
                'ville' => 'Marseille',
                'latitude' => 43.2697,
                'longitude' => 5.3950
            ],
            [
                'nom' => 'Pigalle',
                'rue' => 'Rue du moulin rouge',
                'ville' => 'Paris',
                'latitude' => 43.2697,
                'longitude' => 5.3950
            ],
            [
                'nom' => 'Seventies',
                'rue' => 'Rue François Verdier',
                'ville' => 'Toulouse',
                'latitude' => 43.2697,
                'longitude' => 5.3950
            ],
        ];

        foreach ($lieuxData as $data) {
            $lieu = new Lieu();
            $lieu->setNomLieu($data['nom']);
            $lieu->setRue($data['rue']);
            $lieu->setLatitude($data['latitude']);
            $lieu->setLongitude($data['longitude']);
            $lieu->setVille($this->getReference('ville-' . strtolower($data['ville']), Ville::class));

            $manager->persist($lieu);
            $manager->flush();

            $this->addReference('lieu-' . strtolower(str_replace(' ', '-', $data['nom'])), $lieu);
        }
    }

    public function getDependencies(): array
    {
        return [
            VillesFixtures::class,
        ];
    }
}

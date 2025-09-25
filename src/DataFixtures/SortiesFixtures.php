<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortiesFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $sorties = [
            [
                'nom' => 'Match PSG',
                'dateDebut' => new \DateTime('+10 days'),
                'duree' => 120,
                'dateCloture' => new \DateTime('+5 days'),
                'nbMax' => 50000,
                'description' => 'Match de foot au Stade de France',
                'organisateur' => 'admin',
                'lieu' => 'lieu-stade-de-france',
                'etat' => 2, // Ouverte
            ],
            [
                'nom' => 'Concert Jazz',
                'dateDebut' => new \DateTime('+15 days'),
                'duree' => 180,
                'dateCloture' => new \DateTime('+12 days'),
                'nbMax' => 300,
                'description' => 'Soirée Jazz au Parc OL',
                'organisateur' => 'user1',
                'lieu' => 'lieu-parc-ol',
                'etat' => 1, // Créée
            ],
            [
                'nom' => 'Marathon Marseille',
                'dateDebut' => new \DateTime('+20 days'),
                'duree' => 360,
                'dateCloture' => new \DateTime('+18 days'),
                'nbMax' => 1000,
                'description' => 'Course annuelle à Marseille, départ du Vélodrome',
                'organisateur' => 'admin',
                'lieu' => 'lieu-vélodrome',
                'etat' => 2, // Ouverte
            ],
            [
                'nom' => 'Conférence Tech',
                'dateDebut' => new \DateTime('+25 days'),
                'duree' => 240,
                'dateCloture' => new \DateTime('+22 days'),
                'nbMax' => 150,
                'description' => 'Conférence sur les nouvelles technologies',
                'organisateur' => 'user1',
                'lieu' => 'lieu-parc-ol',
                'etat' => 1, // Créée
            ],
            [
                'nom' => 'Bowling',
                'dateDebut' => new \DateTime('+25 days'),
                'duree' => 240,
                'dateCloture' => new \DateTime('+22 days'),
                'nbMax' => 1,
                'description' => 'Conférence sur les nouvelles technologies',
                'organisateur' => 'user1',
                'lieu' => 'lieu-parc-ol',
                'etat' => 1, // Créée
            ],
        ];

        foreach ($sorties as $data) {
            $sortie = new Sortie();
            $sortie->setNom($data['nom']);
            $sortie->setDateDebut($data['dateDebut']);
            $sortie->setDuree($data['duree']);
            $sortie->setDateCloture($data['dateCloture']);
            $sortie->setNbInscriptionsMax($data['nbMax']);
            $sortie->setDescriptionInfos($data['description']);
            $sortie->setOrganisateur($this->getReference('participant-' . $data['organisateur'], Participant::class));
            $sortie->setLieu($this->getReference($data['lieu'], Lieu::class));
            $sortie->setEtat($this->getReference('etat-' . $data['etat'] , Etat::class));

            $manager->persist($sortie);
            $manager->flush();

            $this->addReference('sortie-' . strtolower(str_replace(' ', '-', $data['nom'])), $sortie);
        }
    }

    public function getDependencies(): array
    {
        return [
            EtatsFixtures::class,
            LieuxFixtures::class,
            ParticipantsFixtures::class,
        ];
    }
}

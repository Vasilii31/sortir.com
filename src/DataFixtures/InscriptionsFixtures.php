<?php

namespace App\DataFixtures;

use App\Entity\Inscription;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InscriptionsFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager): void
    {
        // Définition des inscriptions
        $inscriptions = [
            [
                'participant' => 'admin',
                'sortie' => 'sortie-match-psg',
                'date' => new \DateTime('-2 days'),
            ],
            [
                'participant' => 'user1',
                'sortie' => 'sortie-match-psg',
                'date' => new \DateTime('-1 day'),
            ],
            [
                'participant' => 'user1',
                'sortie' => 'sortie-concert-jazz',
                'date' => new \DateTime('-3 days'),
            ],
        ];

        foreach ($inscriptions as $data) {
            $inscription = new Inscription();
            $inscription->setParticipant($this->getReference('participant-' . $data['participant'], Participant::class));
            $inscription->setSortie($this->getReference($data['sortie'], Sortie::class));
            $inscription->setDateInscription($data['date']);

            $manager->persist($inscription);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ParticipantsFixtures::class,
            SortiesFixtures::class,
        ];
    }
}

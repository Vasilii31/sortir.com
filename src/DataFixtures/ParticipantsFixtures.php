<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantsFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $participants = [
            ['pseudo' => 'admin', 'nom' => 'Admin', 'prenom' => 'Super', 'mail' => 'admin@example.com', 'admin' => true, 'site' => 'campus de paris'],
            ['pseudo' => 'user1', 'nom' => 'Dupont', 'prenom' => 'Jean', 'mail' => 'user1@example.com', 'admin' => false, 'site' => 'campus de lyon'],
        ];

        foreach ($participants as $data) {
            $p = new Participant();
            $p->setPseudo($data['pseudo']);
            $p->setNom($data['nom']);
            $p->setPrenom($data['prenom']);
            $p->setMail($data['mail']);
            $p->setAdministrateur($data['admin']);
            $p->setActif(true);
//            $p->setSite($this->getReference('site-' . $data['site'], Site::class));
            $p->setSite($this->getReference('site-' . strtolower(str_replace(' ', '-', $data['site'])), Site::class));

            // Mot de passe simple
            $p->setMotDePasse($this->passwordHasher->hashPassword($p, 'password'));

            $manager->persist($p);
            $manager->flush();

            $this->addReference('participant-' . $data['pseudo'], $p);
        }
    }

    public function getDependencies(): array
    {
        return [
            SitesFixtures::class,
        ];
    }
}

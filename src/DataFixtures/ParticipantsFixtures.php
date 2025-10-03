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
            ['pseudo' => 'admin', 'nom' => 'Admin', 'prenom' => 'Super', 'mail' => 'franco.olivier@yahoo.fr', 'admin' => true, 'site' => 'campus de paris'],
            ['pseudo' => 'user1', 'nom' => 'Dupont', 'prenom' => 'Jean', 'mail' => 'user1@example.com', 'admin' => false, 'site' => 'campus de lyon'],
            ['pseudo' => 'user2', 'nom' => 'Germain', 'prenom' => 'André', 'mail' => 'user2@example.com', 'admin' => false, 'site' => 'campus de paris'],
            ['pseudo' => 'user3', 'nom' => 'Baratelli', 'prenom' => 'Sofia', 'mail' => 'user3@example.com', 'admin' => false, 'site' => 'campus de lyon'],
            ['pseudo' => 'user4', 'nom' => 'Gemoni', 'prenom' => 'Sébastien', 'mail' => 'user4@example.com', 'admin' => false, 'site' => 'campus de toulouse'],
            ['pseudo' => 'user5', 'nom' => 'Verona', 'prenom' => 'Thomas', 'mail' => 'user5@example.com', 'admin' => false, 'site' => 'campus de marseille'],
            ['pseudo' => 'user6', 'nom' => 'Khifer', 'prenom' => 'Eustache', 'mail' => 'user6@example.com', 'admin' => false, 'site' => 'campus de marseille'],
            ['pseudo' => 'user7', 'nom' => 'Merlin', 'prenom' => 'Amélie', 'mail' => 'user7@example.com', 'admin' => false, 'site' => 'campus de niort'],
            ['pseudo' => 'user8', 'nom' => 'Sérin', 'prenom' => 'Ludivine', 'mail' => 'user8@example.com', 'admin' => false, 'site' => 'campus de niort'],
            ['pseudo' => 'user9', 'nom' => 'Columbus', 'prenom' => 'Albert', 'mail' => 'user9@example.com', 'admin' => false, 'site' => 'campus de nantes 2'],
            ['pseudo' => 'user10', 'nom' => 'Stenal', 'prenom' => 'Désiré', 'mail' => 'user10@example.com', 'admin' => false, 'site' => 'campus de nantes 2'],
            ['pseudo' => 'user11', 'nom' => 'Getrabien', 'prenom' => 'Hortense', 'mail' => 'user11@example.com', 'admin' => false, 'site' => 'campus de nantes 1'],
            ['pseudo' => 'user12', 'nom' => 'Jules', 'prenom' => 'Jacques', 'mail' => 'user12@example.com', 'admin' => false, 'site' => 'campus de nantes 1'],
            ['pseudo' => 'user13', 'nom' => 'Deloir', 'prenom' => 'Thomas', 'mail' => 'user13@example.com', 'admin' => false, 'site' => 'campus de toulouse'],
            ['pseudo' => 'user14', 'nom' => 'Delormeau', 'prenom' => 'Phillibert', 'mail' => 'user14@example.com', 'admin' => false, 'site' => 'campus de niort'],
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

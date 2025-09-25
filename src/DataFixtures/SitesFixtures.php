<?php

namespace App\DataFixtures;

use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SitesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $sites = ['Campus de Niort', 'Campus de Paris', 'Campus de Lyon' , 'Campus de Marseille', 'Campus de Toulouse', 'Campus de Nantes 1', 'Campus de Nantes 2'];

        foreach ($sites as $key => $nom) {
            $site = new Site();
            $site->setNomSite($nom);

            $manager->persist($site);
            $manager->flush();

            $this->addReference('site-' . strtolower(str_replace(' ', '-', $nom)), $site);

        }
    }
}
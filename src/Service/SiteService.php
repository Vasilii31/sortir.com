<?php

namespace App\Service;

use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;

class SiteService
{
    public function __construct(
        private readonly SiteRepository  $siteRepository
    ) {}

    /**
     * Retourne tous les sites
     */
    public function getAllSites(): array
    {
        return $this->siteRepository->findAll();
    }

    /**
     * Crée un nouveau site
     */
    public function create(string $nomSite): Site
    {
        $site = new Site();
        $site->setNomSite($nomSite);

        $this->em->persist($site);
        $this->em->flush();

        return $site;
    }

    /**
     * Supprime un site
     */
    public function delete(Site $site): void
    {
        //Logique métier : on ne supprime pas si des activités utilisent ce site
        $this->siteRepository->remove($site);
    }

    /**
     * Recherche par nom
     */
    public function searchByName(string $term): array
    {
        return $this->siteRepository->createQueryBuilder('s')
            ->where('s.nomSite LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->getQuery()
            ->getResult();
    }
}
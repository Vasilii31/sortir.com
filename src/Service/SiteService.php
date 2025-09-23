<?php

namespace App\Service;

use App\Entity\Site;
use App\Repository\SiteRepository;
use App\ServiceResult\Site\DeleteSiteResult;
use App\ServiceResult\Site\UpdateSiteResult;

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
//    public function create(string $nomSite): Site
//    {
//        $site = new Site();
//        $site->setNomSite($nomSite);
//
//        $this->em->persist($site);
//        $this->em->flush();
//
//        return $site;
//    }
    public function createSite(string $nomSite): Site
    {
        $site = new Site();
        $site->setNomSite($nomSite);

        $this->siteRepository->save($site);

        return $site;
    }


    /**
     * Supprime un site
     */
    public function deleteSite(Site $site): DeleteSiteResult
    {
        // Vérifie si des participants sont liés à ce site
        if (count($site->getParticipants()) > 0) {
            return DeleteSiteResult::SITE_IN_USE;
        }

        // (Tu peux ajouter d’autres checks ici, ex: sorties associées)
        $this->siteRepository->remove($site);

        return DeleteSiteResult::SUCCESS;
    }

    public function updateSite(Site $site, string $newNom): UpdateSiteResult
    {
//        $existing = $this->siteRepository->findOneBy(['nom_site' => $newNom]);
//
//        if ($existing && $existing->getId() !== $site->getId()) {
//            return UpdateSiteResult::NAME_ALREADY_USED;
//        }

        $site->setNomSite($newNom);
        $this->siteRepository->save($site);

        return UpdateSiteResult::SUCCESS;
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
<?php

namespace App\Service;

use App\Repository\SortieRepository;

class SortieService
{
    private SortieRepository $sortieRepository;

    public function __construct(SortieRepository $sortieRepository)
    {
        $this->sortieRepository = $sortieRepository;
    }

    /**
     * Retourne les sorties filtrées selon les critères du formulaire
     */
    public function getFilteredSorties(array $criteria): array
    {
        $qb = $this->sortieRepository->createQueryBuilder('s');

        if (!empty($criteria['nom'])) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%' . $criteria['nom'] . '%');
        }

        if (!empty($criteria['datedebut'])) {
            $qb->andWhere('s.datedebut >= :dateDebut')
                ->setParameter('dateDebut', $criteria['datedebut']);
        }

        if (!empty($criteria['datecloture'])) {
            $qb->andWhere('s.datecloture <= :dateCloture')
                ->setParameter('dateCloture', $criteria['datecloture']);
        }

        if (!empty($criteria['descriptionInfos'])) {
            $qb->andWhere('s.descriptioninfos LIKE :desc')
                ->setParameter('desc', '%' . $criteria['descriptionInfos'] . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function findAll(): array
    {
        return $this->sortieRepository->findAll();
    }
    public function findAllWithSubscribed(): array
    {
        return $this->sortieRepository->findAllWithSubscribed();
    }
}
<?php

namespace App\Repository;

use App\Dto\SortieInscritsDTO;
use App\Entity\Sortie;
use Doctrine\Persistence\ManagerRegistry;

class SortieRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    // Renvoie les sorties
    public function findAll(): array
    {
        $result = $this->createQueryBuilder('s')
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();
        return $result;
    }

    // Renvoie les sorties avec le nombre d'inscrits
    public function findAllWithSubscribed(): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.inscriptions', 'i')
            ->leftJoin('s.etat', 'e')
            ->addSelect('COUNT(i.id) as nbInscrits')
            ->addSelect('e.libelle AS etatLibelle')
            ->groupBy('s.id')
            ->addGroupBy('e.id')
            ->orderBy('s.id', 'ASC');

        $results = $qb->getQuery()->getResult();

        return array_map(
            fn($row) => new SortieInscritsDTO(
                $row[0],
                (int)$row['nbInscrits'],
            ),
            $results
        );
    }

    // Renvoie les sorties filtrées
    public function FindByFilter(array $searchCriteria): array
    {
        $queryBuilder = $this->createQueryBuilder('s')
            ->leftJoin('s.inscriptions', 'i')
            ->leftJoin('s.etat', 'e')
            ->addSelect('COUNT(i.id) as nbInscrits')
            ->addSelect('e.libelle AS etatLibelle')
            ->groupBy('s.id')
            ->addGroupBy('e.id')
            ->orderBy('s.id', 'ASC');

        if (!empty($searchCriteria['nom'])) {
            $queryBuilder->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%' . $searchCriteria['nom'] . '%');
        }
        if (!empty($searchCriteria['datedebut'])) {
            $queryBuilder->andWhere('s.datedebut >= :datedebut')
                ->setParameter('datedebut', $searchCriteria['datedebut']); // déjà un DateTime
        }

        if (!empty($searchCriteria['datecloture'])) {
            $queryBuilder->andWhere('s.datecloture <= :datecloture')
                ->setParameter('datecloture', $searchCriteria['datecloture']); // déjà un DateTime
        }

        $results = $queryBuilder->getQuery()->getResult();

        return array_map(
            fn($row) => new SortieInscritsDTO(
                $row[0],
                (int)$row['nbInscrits'],
            ),
            $results
        );

    }

}

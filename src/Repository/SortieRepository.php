<?php

namespace App\Repository;

use App\Dto\SortieInscritsDTO;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Persistence\ManagerRegistry;
use http\Client\Curl\User;

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
        return $this->createQueryBuilder('s')
            ->leftJoin('s.inscriptions', 'i')
            ->leftJoin('s.etat', 'e')
            ->addSelect('COUNT(i.id) AS nbInscrits')
            ->groupBy('s.id')
            ->getQuery()
            ->getResult();
    }


    // Renvoie les sorties filtrées
    public function findByFilter(array $criteria): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.inscriptions', 'i')
            ->leftJoin('s.etat', 'e')
            ->addSelect('COUNT(i.id) AS nbInscrits')
            ->addSelect('e.libelle AS etatLibelle')
            ->groupBy('s.id')
            ->addGroupBy('e.id')
            ->orderBy('s.id', 'ASC');

        if (!empty($criteria['nom'])) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%' . $criteria['nom'] . '%');
        }
        if (!empty($criteria['datedebut'])) {
            $qb->andWhere('s.datedebut >= :datedebut')
                ->setParameter('datedebut', $criteria['datedebut']);
        }
        if (!empty($criteria['datecloture'])) {
            $qb->andWhere('s.datecloture <= :datecloture')
                ->setParameter('datecloture', $criteria['datecloture']);
        }

        return $qb->getQuery()->getResult();
    }



}

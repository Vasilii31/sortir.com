<?php

namespace App\Repository;

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
        return $this->createQueryBuilder('s')
            ->leftJoin('s.inscriptions', 'i')
            ->leftJoin('s.etat', 'e')
            ->addSelect('COUNT(i.id) AS nbInscrits')
            ->where('e.libelle NOT IN (:excludedEtats)')
            ->setParameter('excludedEtats', ['Annulée', 'Historisée'])
            ->groupBy('s.id')
            ->getQuery()
            ->getResult();
    }


    /**
     * Récupère une sortie avec toutes ses relations (participants inclus).
     */
    public function findWithParticipants(int $id): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.Lieu', 'l')
            ->addSelect('l')
            ->leftJoin('l.ville', 'v')
            ->addSelect('v')
            ->leftJoin('s.inscriptions', 'i')
            ->leftJoin('i.participant', 'p')
            ->leftJoin('s.etat', 'e')
            ->addSelect('s', 'e')
            ->addSelect('i', 'p')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
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

<?php

namespace App\Repository;

use App\Entity\Participant;
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


    // Renvoie les sorties filtrées
    public function findByFilter(array $criteria, Participant $user): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.inscriptions', 'i')
            ->leftJoin('s.organisateur', 'o')
            ->leftJoin('o.site', 'site')
            ->leftJoin('s.etat', 'e')
            ->addSelect('COUNT(i.id) AS nbInscrits')
            ->addSelect('e.libelle AS etatLibelle')
            ->groupBy('s.id')
            ->addGroupBy('e.id')
            ->orderBy('s.id', 'ASC');

        // Filtre site
        if (!empty($criteria['site'])) {
            $qb->andWhere('site.id = :siteId')
                ->setParameter('siteId', $criteria['site']);
        }

        // Filtre nom (LIKE pour correspondance partielle)
        if (!empty($criteria['nom'])) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%' . $criteria['nom'] . '%');
        }

        // Filtre dates
        if (!empty($criteria['datedebut'])) {
            $qb->andWhere('s.datedebut >= :datedebut')
                ->setParameter('datedebut', $criteria['datedebut']);
        }
        if (!empty($criteria['datecloture'])) {
            $qb->andWhere('s.datecloture <= :datecloture')
                ->setParameter('datecloture', $criteria['datecloture']);
        }

        // Checkbox : sorties dont je suis l’organisateur
        if (!empty($criteria['sortieCreator'])) {
            $qb->andWhere('s.organisateur = :user')
                ->setParameter('user', $user);
        }

        // Sorties auxquelles je suis inscrit
        if (!empty($criteria['sortieInscrit'])) {
            $qb->andWhere(':user MEMBER OF s.inscriptions')
                ->setParameter('user', $user);
        }

        // Sorties auxquelles je ne suis pas inscrit
        if (!empty($criteria['sortieNonInscrit'])) {
            $qb->andWhere(':user NOT MEMBER OF s.inscriptions')
                ->setParameter('user', $user);
        }

        // Sorties passées
        if (!empty($criteria['sortiesPassees'])) {
            $qb->andWhere('s.datedebut < :now')
                ->setParameter('now', new \DateTime());
        } else {
            // Si non cochée, on ne renvoie que les sorties futures
            $qb->andWhere('s.datedebut >= :now')
                ->setParameter('now', new \DateTime());
        }

        return $qb->getQuery()->getResult();
    }





}

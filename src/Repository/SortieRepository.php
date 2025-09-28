<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\Site;
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


    public function findByFilter(array $criteria, Participant $user = null): array
    {
        // Construction de base de la requête
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.inscriptions', 'i')
            ->leftJoin('s.organisateur', 'o')
            ->leftJoin('o.site', 'site')
            ->leftJoin('s.etat', 'e')
            ->addSelect('COUNT(DISTINCT i.id) AS nbInscrits')
            ->addSelect('e.libelle AS etatLibelle')
            ->groupBy('s.id')
            ->addGroupBy('e.id')
            ->orderBy('s.datedebut', 'DESC');

        // Filtre par nom
        if (!empty($criteria['nom'])) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%' . $criteria['nom'] . '%');
        }

        // Filtre par site
        if (!empty($criteria['site'])) {
            $siteId = $criteria['site'] instanceof Site ? $criteria['site']->getId() : $criteria['site'];
            $qb->andWhere('site.id = :siteId')
                ->setParameter('siteId', $siteId);
        }

        // Filtre par date de début
        if (!empty($criteria['datedebut']) && $criteria['datedebut'] instanceof \DateTimeInterface) {
            $qb->andWhere('s.datedebut >= :datedebut')
                ->setParameter('datedebut', $criteria['datedebut']);
        }

        // Filtre par date de clôture
        if (!empty($criteria['datecloture']) && $criteria['datecloture'] instanceof \DateTimeInterface) {
            $qb->andWhere('s.datecloture <= :datecloture')
                ->setParameter('datecloture', $criteria['datecloture']);
        }

        // Filtre sorties créées par l'utilisateur
        if (!empty($criteria['sortieCreator']) && $user) {
            $qb->andWhere('s.organisateur = :user')
                ->setParameter('user', $user);
        }

        // Filtre sorties où l'utilisateur est inscrit
        if (!empty($criteria['sortieInscrit']) && $user) {
            $qb->innerJoin('s.inscriptions', 'i_user', 'WITH', 'i_user.participant = :user_inscrit')
                ->setParameter('user_inscrit', $user);
        }

        // Filtre sorties où l'utilisateur n'est pas inscrit
        if (!empty($criteria['sortieNonInscrit']) && $user) {
            $qb->andWhere('s.id NOT IN (
            SELECT s2.id FROM App\Entity\Sortie s2
            INNER JOIN s2.inscriptions i2
            WHERE i2.participant = :user_non_inscrit
        )')
                ->setParameter('user_non_inscrit', $user);
        }

        // Filtre sorties passées (état = 5)
        if (!empty($criteria['sortiesPassees'])) {
            $qb->andWhere('s.etat = :etatPasse')
                ->setParameter('etatPasse', 5);
        }

        $query = $qb->getQuery();
        return $query->getResult();
    }



//    public function findByFilter(array $criteria, Participant $user = null): array
//    {
//        // Construction de base de la requête
//        $qb = $this->createQueryBuilder('s')
//            ->leftJoin('s.inscriptions', 'i')
//            ->leftJoin('s.organisateur', 'o')
//            ->leftJoin('o.site', 'site')
//            ->leftJoin('s.etat', 'e')
//            ->addSelect('COUNT(DISTINCT i.id) AS nbInscrits')
//            ->addSelect('e.libelle AS etatLibelle')
//            ->groupBy('s.id')
//            ->addGroupBy('e.id')
//            ->orderBy('s.datedebut', 'DESC');
//
//        // Test du filtre inscriptions avec méthode EXISTS plus propre
//        if (!empty($criteria['sortiesPassees'])) {
//            // Filtre pour sorties dont l'état a l'id 5 (passées)
//            $qb->andWhere('s.etat = :etatPasse')
//                ->setParameter('etatPasse', 5);
//        }
//
//
//        $query = $qb->getQuery();
//        $results = $query->getResult();
//
//        dd('ÉTAPE 9 - Sortie passées', [
//            'user_id' => $user ? $user->getId() : null,
//            'sortiesPassees' => $criteria['sortiesPassees'] ?? false,
//            'nombre_résultats' => count($results),
//            'results' => $results,
//          ]);
//    }


}

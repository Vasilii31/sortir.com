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
    public function findAllWithSubscribed(Participant $user): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.inscriptions', 'i')
            ->leftJoin('s.etat', 'e')
            ->addSelect('COUNT(i.id) AS nbInscrits')
            ->where('e.libelle NOT IN (:excludedEtats)')
            ->setParameter('excludedEtats', ['Annulée', 'Historisée'])
            ->andWhere('s.isPrivate = false OR (s.isPrivate = true AND :user MEMBER OF s.participantsPrives)
                                                    OR :isAdmin = true')
            ->setParameter('user', $user)
            ->setParameter('isAdmin', in_array('ROLE_ADMIN', $user->getRoles() ?? []))
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
            ->where('s.isPrivate = false OR (s.isPrivate = true AND :user MEMBER OF s.participantsPrives)
                                                    OR :isAdmin = true')
            ->setParameter('user', $user)
            ->setParameter('isAdmin', in_array('ROLE_ADMIN', $user->getRoles() ?? []))
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


    /**
     * Trouve les sorties futures ou en cours organisées par un participant
     */
    public function findFutureOrOngoingByOrganizer($organizer): array
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('s')
            ->where('s.organisateur = :organizer')
            ->andWhere('s.datedebut > :now OR (s.duree IS NOT NULL AND DATE_ADD(s.datedebut, s.duree, \'MINUTE\') > :now) OR s.duree IS NULL')
            ->setParameter('organizer', $organizer)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }

    /**
     * Supprime des sorties
     */
    public function removeSorties(array $sorties): void
    {
        foreach ($sorties as $sortie) {
            $this->getEntityManager()->remove($sortie);
        }
        $this->getEntityManager()->flush();
    }


    // SortieRepository.php
    public function findWithSubscribedBySite(Participant $participant)
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.organisateur', 'o')  // jointure sur l'organisateur
            ->innerJoin('o.site', 'site')       // jointure sur le site du participant
            ->andWhere('site = :site')
            ->setParameter('site', $participant->getSite())
            ->orderBy('s.datedebut', 'ASC')
            ->getQuery()
            ->getResult();
    }






}

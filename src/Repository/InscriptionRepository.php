<?php

namespace App\Repository;

use App\Entity\Inscription;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inscription>
 */
class InscriptionRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inscription::class);
    }

    public function isParticipantInscrit(Sortie $sortie, Participant $participant): bool
    {
        $qb = $this->createQueryBuilder('i')
            ->select('count(i.id)')
            ->where('i.sortie = :sortie')
            ->andWhere('i.participant = :participant')
            ->setParameters([
                'sortie' => $sortie,
                'participant' => $participant
            ])
            ->getQuery();

        return $qb->getSingleScalarResult() > 0;
    }
    //    /**
    //     * @return Inscription[] Returns an array of Inscription objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }
    /**
     * Trouve les inscriptions futures ou en cours d'un participant
     */
    public function findFutureOrOngoingByParticipant($participant): array
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('i')
            ->join('i.Sortie', 's')
            ->where('i.participant = :participant')
            ->andWhere('s.datedebut > :now OR (s.duree IS NOT NULL AND DATE_ADD(s.datedebut, s.duree, \'MINUTE\') > :now) OR s.duree IS NULL')
            ->setParameter('participant', $participant)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }

    /**
     * Supprime des inscriptions
     */
    public function removeInscriptions(array $inscriptions): void
    {
        foreach ($inscriptions as $inscription) {
            $this->getEntityManager()->remove($inscription);
        }
        $this->getEntityManager()->flush();
    }

    public function save($entity, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        if ($flush) {
            $em->flush();
        }
    }

    public function remove($entity, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->remove($entity);
        if ($flush) {
            $em->flush();
        }
    }
}

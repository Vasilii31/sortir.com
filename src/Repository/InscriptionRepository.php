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

    //    public function findOneBySomeField($value): ?Inscription
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

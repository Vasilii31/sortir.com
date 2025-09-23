<?php

namespace App\Repository;

use App\Dto\SortieInscritsDTO;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findAll(): array
        {
            $result =  $this->createQueryBuilder('s')
                ->orderBy('s.id', 'ASC')
                ->getQuery()
                ->getResult()
            ;
            return $result;
        }
    public function findAllWithSubscribed(): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.inscriptions', 'i')
            ->addSelect('COUNT(i.id) as nbInscrits')
            ->groupBy('s.id')
            ->orderBy('s.id', 'ASC');

        $results = $qb->getQuery()->getResult();

        $toReturn =  array_map(fn($row) =>
        new SortieInscritsDTO($row[0], (int)$row['nbInscrits']),
            $results
        );
        return $toReturn;

    }

    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

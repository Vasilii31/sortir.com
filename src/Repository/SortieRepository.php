<?php

namespace App\Repository;

use App\Dto\SortieInscritsDTO;
use App\Entity\Sortie;
use Doctrine\Persistence\ManagerRegistry;

class SortieRepository  extends BaseRepository
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
                $row['etatLibelle'] // ajouter le libellé dans le DTO
            ),
            $results
        );
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

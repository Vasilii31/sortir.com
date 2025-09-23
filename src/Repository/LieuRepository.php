<?php

namespace App\Repository;

use App\Dto\SortieInscritsDTO;
use App\Entity\Lieu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lieu>
 */
class LieuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lieu::class);
    }

    public function findAll() : array
    {

        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.ville', 'v')
            ->addSelect('v') // très important : pour charger aussi l'entité Ville
            ->orderBy('l.id', 'ASC');
//        dd( $qb->getQuery()->getResult());

        return $qb->getQuery()->getResult();

    }

}

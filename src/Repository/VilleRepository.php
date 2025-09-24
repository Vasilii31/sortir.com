<?php

namespace App\Repository;

use App\Entity\Ville;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ville>
 */
class VilleRepository  extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ville::class);
    }
    public function findAll() : array
    {

        $qb = $this->createQueryBuilder('v')
            ->orderBy('v.id', 'ASC');

        return $qb->getQuery()->getResult();

    }
}

<?php

namespace App\Repository;

use App\Entity\Receta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Receta>
 */
class RecetaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Receta::class);
    }

    /**
    * @return Receta[] Returns an array of Receta objects, that fits by type
    */
    public function findByType($tipo): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.tipo = :val')
            ->setParameter('val', $tipo)
            ->getQuery()
            ->getResult()
        ;
    }

    //    public function findOneBySomeField($value): ?Receta
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

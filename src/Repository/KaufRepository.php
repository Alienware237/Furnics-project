<?php

namespace okpt\furnics\project\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use okpt\furnics\project\Entity\Kauf;

/**
 * @extends ServiceEntityRepository<Kauf>
 *
 * @method Kauf|null find($id, $lockMode = null, $lockVersion = null)
 * @method Kauf|null findOneBy(array $criteria, array $orderBy = null)
 * @method Kauf[]    findAll()
 * @method Kauf[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KaufRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Kauf::class);
    }

    //    /**
    //     * @return Kauf[] Returns an array of Kauf objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('k')
    //            ->andWhere('k.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('k.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Kauf
    //    {
    //        return $this->createQueryBuilder('k')
    //            ->andWhere('k.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

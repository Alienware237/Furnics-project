<?php

namespace okpt\furnics\project\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use okpt\furnics\project\Entity\Orders;

/**
 * @extends ServiceEntityRepository<Orders>
 *
 * @method Orders|null find($id, $lockMode = null, $lockVersion = null)
 * @method Orders|null findOneBy(array $criteria, array $orderBy = null)
 * @method Orders[]    findAll()
 * @method Orders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Orders::class);
    }

    //    /**
    //     * @return Orders[] Returns an array of Orders objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Orders
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findCurrentOrder($user, $currentPlace): ?Orders
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.user = :u')
            ->andWhere('o.currentPlace = :cPl')
            ->setParameter('u', $user)
            ->setParameter('cPl', $currentPlace)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOpenOder($user): ?Orders
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.user = :u')
            ->andWhere('o.currentPlace != :cPl')
            ->setParameter('u', $user)
            ->setParameter('cPl', 'ordered')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

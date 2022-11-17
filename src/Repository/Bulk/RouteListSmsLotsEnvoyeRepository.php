<?php

namespace App\Repository\Bulk;

use App\Entity\Bulk\RouteListSmsLotsEnvoye;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RouteListSmsLotsEnvoye|null find($id, $lockMode = null, $lockVersion = null)
 * @method RouteListSmsLotsEnvoye|null findOneBy(array $criteria, array $orderBy = null)
 * @method RouteListSmsLotsEnvoye[]    findAll()
 * @method RouteListSmsLotsEnvoye[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RouteListSmsLotsEnvoyeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RouteListSmsLotsEnvoye::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(RouteListSmsLotsEnvoye $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(RouteListSmsLotsEnvoye $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return RouteListSmsLotsEnvoye[] Returns an array of RouteListSmsLotsEnvoye objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RouteListSmsLotsEnvoye
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

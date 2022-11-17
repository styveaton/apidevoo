<?php

namespace App\Repository\Bulk;

use App\Entity\Bulk\ListSmsLotsEnvoye;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ListSmsLotsEnvoye|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListSmsLotsEnvoye|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListSmsLotsEnvoye[]    findAll()
 * @method ListSmsLotsEnvoye[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListSmsLotsEnvoyeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListSmsLotsEnvoye::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(ListSmsLotsEnvoye $entity, bool $flush = true): void
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
    public function remove(ListSmsLotsEnvoye $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return ListSmsLotsEnvoye[] Returns an array of ListSmsLotsEnvoye objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ListSmsLotsEnvoye
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

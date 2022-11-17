<?php

namespace App\Repository\Account;

use App\Entity\Account\ListCommissionTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListCommissionTransaction>
 *
 * @method ListCommissionTransaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListCommissionTransaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListCommissionTransaction[]    findAll()
 * @method ListCommissionTransaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListCommissionTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListCommissionTransaction::class);
    }

    public function add(ListCommissionTransaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ListCommissionTransaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ListCommissionTransaction[] Returns an array of ListCommissionTransaction objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ListCommissionTransaction
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

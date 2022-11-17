<?php

namespace App\Repository\Bulk;

use App\Entity\Bulk\ListNotificationModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListNotificationModel>
 *
 * @method ListNotificationModel|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListNotificationModel|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListNotificationModel[]    findAll()
 * @method ListNotificationModel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListNotificationModelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListNotificationModel::class);
    }

    public function add(ListNotificationModel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ListNotificationModel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ListNotificationModel[] Returns an array of ListNotificationModel objects
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

//    public function findOneBySomeField($value): ?ListNotificationModel
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

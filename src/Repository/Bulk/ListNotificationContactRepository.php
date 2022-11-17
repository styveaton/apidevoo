<?php

namespace App\Repository\Bulk;

use App\Entity\Bulk\ListNotificationContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListNotificationContact>
 *
 * @method ListNotificationContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListNotificationContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListNotificationContact[]    findAll()
 * @method ListNotificationContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListNotificationContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListNotificationContact::class);
    }

    public function add(ListNotificationContact $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ListNotificationContact $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ListNotificationContact[] Returns an array of ListNotificationContact objects
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

//    public function findOneBySomeField($value): ?ListNotificationContact
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

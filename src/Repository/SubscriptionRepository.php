<?php

namespace App\Repository;

use App\Entity\Subscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subscription>
 */
class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    public function findSubByMe(int $userId): array
    {
        // QueryBuilder pour joindre toutes les tables nécessaires
        $qb = $this->createQueryBuilder('s')
            ->select('s', 'st', 'p', 'pi', 'pl')
            ->join('s.subscriptionType', 'st')
            ->join('st.product', 'p')
            ->leftJoin('p.productImages', 'pi')
            ->leftJoin('p.productLangages', 'pl', 'WITH', 'pl.code = :lang') // On suppose code = 'FR'
            ->where('s.user = :userId')
            ->andWhere('s.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', 'available')
            ->setParameter('lang', 'FR');

        return $qb->getQuery()->getResult();
    }



//    /**
//     * @return Subscription[] Returns an array of Subscription objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Subscription
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

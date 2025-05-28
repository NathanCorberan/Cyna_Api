<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Retourne tous les produits avec leurs relations
     * @return Product[]
     */
    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.productImages', 'images')->addSelect('images')
            ->leftJoin('p.productLangages', 'langages')->addSelect('langages')
            ->leftJoin('p.subscriptionTypes', 'subscriptions')->addSelect('subscriptions')
            ->leftJoin('p.category', 'category')->addSelect('category')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les 3 produits les plus commandés
     * @return Product[]
     */
    public function findTopOrderedProductsObselete(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.orders', 'o') 
            ->groupBy('p.id') 
            ->orderBy('COUNT(o.id)', 'DESC') 
            ->setMaxResults(3) 
            ->getQuery()
            ->getResult();
    }

    public function findTopOrderedProducts(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p, SUM(oi.quantity) AS HIDDEN totalQuantity')
            ->join('p.orderItems', 'oi')
            ->join('oi.order', 'o')
            ->where('o.status = :status')
            ->setParameter('status', 'payed')
            ->groupBy('p.id')
            ->orderBy('totalQuantity', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findOneWithRelations(int $id): ?Product
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')->addSelect('c')
            ->leftJoin('p.productLangages', 'pl')->addSelect('pl')
            ->leftJoin('p.productImages', 'pi')->addSelect('pi')
            ->leftJoin('p.subscriptionTypes', 'st')->addSelect('st')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

}

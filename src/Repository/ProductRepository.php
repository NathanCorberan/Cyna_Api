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
            ->join('p.orderItems', 'oi')
            ->groupBy('p.id')
            ->orderBy('SUM(oi.quantity)', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult(); // ✅ retourne directement des objets Product
    }
}

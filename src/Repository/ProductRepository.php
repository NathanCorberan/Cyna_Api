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
}

<?php

namespace App\Application\State\Product;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\ProductRepository;

class TopOrdersProductProvider implements ProviderInterface
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $products = $this->productRepository->findTopOrderedProducts();
        $formattedProducts = [];

        foreach ($products as $product) {
            $formattedProducts[] = [
                'id' => $product->getId(),
                'available_stock' => $product->getAvailableStock(),
                'creation_date' => $product->getCreationDate(),
                'status' => $product->getStatus(),
                'category_name' => $product->getCategory() ? $product->getCategory()->getName() : null,
                'productLangages' => array_map(function ($langage) {
                    return [
                        'id' => $langage->getId(),
                        'code' => $langage->getCode(),
                        'name' => $langage->getName(),
                        'description' => $langage->getDescription(),
                    ];
                }, $product->getProductLangages()->toArray()),
                'productImages' => array_map(function ($image) {
                    return [
                        'id' => $image->getId(),
                        'image_link' => $image->getImageLink(),
                        'name' => $image->getName(),
                    ];
                }, $product->getProductImages()->toArray()),
                'subscriptionTypes' => array_map(function ($subscription) {
                    return [
                        'id' => $subscription->getId(),
                        'type' => $subscription->getType(),
                        'price' => $subscription->getPrice() . "€",
                    ];
                }, $product->getSubscriptionTypes()->toArray()),
            ];
        }

        return $formattedProducts;
    }
}

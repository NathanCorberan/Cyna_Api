<?php

namespace App\Application\State\Category;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\ProductRepository;
use App\Dto\Product\ProductDetailsOutputDto;
use App\Dto\Product\ProductLangageOutputDto;
use App\Dto\Product\ProductImageOutputDto;
use App\Dto\Subscription\SubscriptionTypeOutputDto;

class CategoryProductsProvider implements ProviderInterface
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @return ProductDetailsOutputDto[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $categoryId = $uriVariables['id'] ?? null;
        if (!$categoryId) {
            return [];
        }

        $products = $this->productRepository->findBy(['category' => $categoryId]);
        $dtos = [];

        foreach ($products as $product) {
            $langDtos = [];
            foreach ($product->getProductLangages() as $lang) {
                $langDtos[] = [
                    'id' => $lang->getId(),
                    'code' => $lang->getCode(),
                    'name' => $lang->getName(),
                    'description' => $lang->getDescription(),
                ];
            }
            $imgDtos = [];
            foreach ($product->getProductImages() as $img) {
                $imgDtos[] = [
                    'id' => $img->getId(),
                    'image_link' => $img->getImageLink(),
                    'name' => $img->getName(),
                ];
            }
            $subDtos = [];
            foreach ($product->getSubscriptionTypes() as $sub) {
                $subDtos[] = [
                    'id' => $sub->getId(),
                    'type' => $sub->getType(),
                    'price' => $sub->getPrice() . '€',
                ];
            }

            $dtos[] = [
                'id' => $product->getId(),
                'available_stock' => $product->getAvailableStock(),
                'creation_date' => $product->getCreationDate(),
                'status' => $product->getStatus(),
                'category_name' => $product->getCategory()->getName(),
                'productLangages' => $langDtos,
                'productImages' => $imgDtos,
                'subscriptionTypes' => $subDtos,
            ];
        }

        return $dtos;

    }
}

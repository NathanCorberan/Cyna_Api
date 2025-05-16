<?php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\ProductRepository;

class CategoryProductsProvider implements ProviderInterface
{
    public function __construct(private ProductRepository $productRepository) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $categoryId = $uriVariables['id'];
        return $this->productRepository->findBy(['category' => $categoryId]);
    }
}

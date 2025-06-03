<?php

namespace App\Application\State\Category;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\CategoryRepository;

class CategoryCollectionProvider implements ProviderInterface
{
    public function __construct(private CategoryRepository $categoryRepository) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $categories = $this->categoryRepository->findAllCategories();
        $result = [];

        foreach ($categories as $category) {
            // Construction du tableau de languages
            $langs = [];
            foreach ($category->getCategoryLanguages() as $cl) {
                $langs[] = [
                    'code' => $cl->getCode(),
                    'name' => $cl->getName(),
                    'description' => $cl->getDescription(),
                ];
            }

            $nbProducts = $category->getProducts()->count();
            // Image principale (optionnel)
            $imageLink = $category->getImageLink();

            $result[] = [
                '@id' => '/api/categories/' . $category->getId(),
                '@type' => 'Category',
                'id' => $category->getId(),
                'name' => $category->getName(),
                'creation_date' => $category->getCreationDate(),
                'category_order' => $category->getCategoryOrder(),
                'categoryLanguages' => $langs,
                'nbProducts' => $nbProducts,
                'imageLink' => $imageLink,
            ];
        }
        return $result;
    }
}

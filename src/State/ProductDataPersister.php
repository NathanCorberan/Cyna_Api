<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product;
use App\Entity\Category;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductDataPersister implements ProcessorInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Product
    {
        if (!$data instanceof Product) {
            throw new \InvalidArgumentException('Invalid data type');
        }

        // ✅ Associer `category_id` à une catégorie
        $categoryId = $data->getCategoryId();

        if ($categoryId) {
            $category = $this->entityManager->getRepository(Category::class)->find($categoryId);

            if (!$category) {
                throw new NotFoundHttpException("Category with ID '$categoryId' not found.");
            }

            $data->setCategory($category);
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}

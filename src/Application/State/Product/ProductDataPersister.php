<?php
namespace App\Application\State\Product;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Product;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductDataPersister implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProcessorInterface $decoratedProcessor
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Product) {
            return $this->decoratedProcessor->process($data, $operation, $uriVariables, $context);
        }

        // Traitement uniquement pour POST et PATCH
        if (in_array($operation->getMethod(), ['POST', 'PATCH'])) {
            $categoryId = $data->getCategoryId();
            if ($categoryId !== null) {
                $category = $this->entityManager->getRepository(Category::class)->find($categoryId);
                if (!$category) {
                    throw new NotFoundHttpException("Category with ID '$categoryId' not found.");
                }
                $data->setCategory($category);
            }
        }

        return $this->decoratedProcessor->process($data, $operation, $uriVariables, $context);
    }
}


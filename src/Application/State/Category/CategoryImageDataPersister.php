<?php

namespace App\Application\State\Category;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\CategoryImage;
use App\Entity\Category;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryImageDataPersister implements ProcessorInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CategoryImage
    {
        if (!$data instanceof CategoryImage) {
            throw new \InvalidArgumentException('Invalid data type');
        }

        // Récupération de l'ID de la catégorie depuis la requête
        $categoryId = $data->getCategoryId();

        if ($categoryId) {
            // Recherche de la catégorie en base de données
            $category = $this->entityManager->getRepository(Category::class)->find($categoryId);

            if (!$category) {
                throw new NotFoundHttpException("Category with ID '$categoryId' not found.");
            }

            // Attribution de la catégorie trouvée
            $data->setCategory($category);
        }

        // Sauvegarde en base
        $this->entityManager->persist($data);
        $this->entityManager->flush();

        // ✅ Retourne l'objet sauvegardé
        return $data;
    }
}

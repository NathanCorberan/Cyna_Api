<?php

namespace App\State;

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

        // Récupération du nom de la catégorie depuis la requête
        $categoryName = $data->getCategoryName();

        if ($categoryName) {
            // Recherche de la catégorie en base de données
            $category = $this->entityManager->getRepository(Category::class)->findOneBy(['name' => $categoryName]);

            if (!$category) {
                throw new NotFoundHttpException("Category '$categoryName' not found.");
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

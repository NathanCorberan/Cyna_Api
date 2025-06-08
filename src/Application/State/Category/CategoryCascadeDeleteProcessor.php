<?php

namespace App\Application\State\Category;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Category;
use Symfony\Component\HttpFoundation\File\File;

class CategoryCascadeDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private string $projectDir
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Category) {
            return null;
        }

        // 1. Suppression des images liées (+ fichiers)
        foreach ($data->getCategoryImages() as $img) {
            $imgPath = $this->projectDir . '/public/assets/images/categories/' . $img->getImageLink();
            if (is_file($imgPath)) {
                @unlink($imgPath);
            }
            $this->em->remove($img);
        }
        $data->getCategoryImages()->clear();

        // 2. Suppression des traductions
        foreach ($data->getCategoryLanguages() as $lang) {
            $this->em->remove($lang);
        }
        $data->getCategoryLanguages()->clear();

        // 3. (Optionnel) Détacher les produits si tu ne veux pas les delete
        foreach ($data->getProducts() as $product) {
            $product->setCategory(null);
        }
        $data->getProducts()->clear();

        // 4. Suppression de la catégorie elle-même
        $this->em->remove($data);
        $this->em->flush();

        return null;
    }
}

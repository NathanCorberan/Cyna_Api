<?php

namespace App\Application\State\Product;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ProductLangage;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ProductLangageDataPersister implements ProcessorInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof ProductLangage) {
            return $data;
        }

        // ✅ Vérifier que `product_id` est bien envoyé
        if ($data->getProductId() !== null) {
            $product = $this->entityManager->getRepository(Product::class)->find($data->getProductId());

            if (!$product) {
                throw new BadRequestException("Le produit avec l'ID {$data->getProductId()} n'existe pas.");
            }

            $data->setProduct($product); // ✅ Associer le produit
            $data->setProductId(null); // ✅ Supprimer `product_id` après l'association
        }

        // ✅ Enregistrer en base
        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}

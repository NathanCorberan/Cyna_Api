<?php

namespace App\Application\State\Product;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\StripeProductManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Product;

class ProductDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StripeProductManager $stripeProductManager,
        private string $projectDir
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        /** @var Product|null $product */
        $product = $data;

        if (!$product) {
            throw new NotFoundHttpException('Produit non trouvé');
        }

        if ($product->getStripeProductId()) {
            try {
                $this->stripeProductManager->deleteStripeProduct($product->getStripeProductId());
            } catch (\Throwable $e) {
            }
        }

        foreach ($product->getProductLangages() as $lang) {
            $this->entityManager->remove($lang);
        }
        foreach ($product->getProductImages() as $img) {
            $filePath = $this->projectDir . '/public/assets/images/products/' . $img->getImageLink();
            if (file_exists($filePath)) {
                unlink($filePath);
             }
            $this->entityManager->remove($img);
        }
        foreach ($product->getSubscriptionTypes() as $sub) {
            $this->entityManager->remove($sub);
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();
    }
}

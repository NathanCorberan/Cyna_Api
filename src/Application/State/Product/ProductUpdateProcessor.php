<?php

namespace App\Application\State\Product;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\Product;
use App\Entity\ProductImage;
use App\Entity\ProductLangage;
use App\Entity\Category;
use App\Service\AzureTranslateService;
use App\Service\StripeProductManager;

class ProductUpdateProcessor implements ProcessorInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private string $projectDir,
        private AzureTranslateService $translator,
        private StripeProductManager $stripeProductManager
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var Product $product */
        $product = $data;
        $request = $this->requestStack->getCurrentRequest();

        $postData = $request->request->all();

        $status = $postData['status'] ?? null;
        $stock = $postData['available_stock'] ?? null;
        $categoryId = $postData['category_id'] ?? null;
        $name = $postData['name'] ?? null;
        $description = $postData['description'] ?? null;
        $lang = $postData['lang'] ?? 'fr';

        $imageFiles = $request->files->get('imageFile');

        if ($status !== null) {
            $product->setStatus($status);
        }
        if ($stock !== null) {
            $product->setAvailableStock((int)$stock);
        }
        if ($categoryId !== null) {
            $category = $this->entityManager->getRepository(Category::class)->find($categoryId);
            if ($category) {
                $product->setCategory($category);
            }
        }

        if (!$imageFiles) {
            $imageFiles = [];
        } elseif (!is_array($imageFiles)) {
            $imageFiles = [$imageFiles];
        }

        $existingImages = $product->getProductImages()->toArray();
        $existingCount = count($existingImages);

        $uploadDir = $this->projectDir . '/public/assets/images/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($imageFiles as $i => $imageFile) {
            if (!$imageFile instanceof UploadedFile) {
                continue;
            }

            $originalName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $originalName);
            $filename = $safeName . '_' . uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move($uploadDir, $filename);

            if ($i < $existingCount) {
                $existingImage = $existingImages[$i];
                $oldFilePath = $uploadDir . $existingImage->getImageLink();
                if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                    unlink($oldFilePath);
                }
                $existingImage->setName($safeName);
                $existingImage->setImageLink($filename);
                $this->entityManager->persist($existingImage);
            } else {
                $productImage = new ProductImage();
                $productImage->setName($safeName);
                $productImage->setImageLink($filename);
                $productImage->setProduct($product);
                $this->entityManager->persist($productImage);
                $product->addProductImage($productImage);
            }
        }

        if (count($imageFiles) < $existingCount) {
            for ($j = count($imageFiles); $j < $existingCount; $j++) {
                $toRemove = $existingImages[$j];
                $oldFilePath = $uploadDir . $toRemove->getImageLink();
                if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                    unlink($oldFilePath);
                }
                $product->removeProductImage($toRemove);
                $this->entityManager->remove($toRemove);
            }
        }

        if ($name !== null && $description !== null) {
            $updated = false;
            foreach ($product->getProductLangages() as $productLang) {
                if ($productLang->getCode() === $lang) {
                    $productLang->setName($name);
                    $productLang->setDescription($description);
                    $updated = true;
                    break;
                }
            }

            if (!$updated) {
                $productLang = new ProductLangage();
                $productLang->setProduct($product);
                $productLang->setCode($lang);
                $productLang->setName($name);
                $productLang->setDescription($description);
                $this->entityManager->persist($productLang);
                $product->addProductLangage($productLang);
            }

            $translations = $this->autoTranslate($name, $description, $lang);

            foreach ($translations as $code => $translation) {
                if ($code === $lang) {
                    continue;
                }

                $existingLang = null;
                foreach ($product->getProductLangages() as $pl) {
                    if ($pl->getCode() === $code) {
                        $existingLang = $pl;
                        break;
                    }
                }

                if ($existingLang) {
                    $existingLang->setName($translation['name']);
                    $existingLang->setDescription($translation['description']);
                } else {
                    $productLang = new ProductLangage();
                    $productLang->setProduct($product);
                    $productLang->setCode($code);
                    $productLang->setName($translation['name']);
                    $productLang->setDescription($translation['description']);
                    $this->entityManager->persist($productLang);
                    $product->addProductLangage($productLang);
                }
            }
        }

        // Modification / ajout des abonnements
        $subscriptionsJson = $request->request->get('subscriptionTypes');

        if ($subscriptionsJson) {
            $subscriptionsData = json_decode($subscriptionsJson, true);
            if (is_array($subscriptionsData)) {
                // Indexe les abonnements existants par type
                $existingSubsByType = [];
                foreach ($product->getSubscriptionTypes() as $existingSub) {
                    $existingSubsByType[$existingSub->getType()] = $existingSub;
                }

                foreach ($subscriptionsData as $data) {
                    if (empty($data['type']) || empty($data['price'])) {
                        continue;
                    }

                    if (isset($existingSubsByType[$data['type']])) {
                        // Modifie l'abonnement existant
                        $sub = $existingSubsByType[$data['type']];
                        $sub->setPrice($data['price']);

                        $stripePriceId = $this->stripeProductManager->createPriceForProduct($product, $data['price'], $data['type']);
                        $sub->setStripePriceId($stripePriceId);

                        $this->entityManager->persist($sub);

                        unset($existingSubsByType[$data['type']]);
                    } else {
                        // Création d'un nouvel abonnement
                        $subscriptionType = new \App\Entity\SubscriptionType();
                        $subscriptionType->setType($data['type']);
                        $subscriptionType->setPrice($data['price']);
                        $subscriptionType->setProduct($product);

                        $stripePriceId = $this->stripeProductManager->createPriceForProduct($product, $data['price'], $data['type']);
                        $subscriptionType->setStripePriceId($stripePriceId);

                        $this->entityManager->persist($subscriptionType);
                        $product->addSubscriptionType($subscriptionType);
                    }
                }

                // Si tu souhaites supprimer les abonnements non reçus, décommente la partie suivante :
                /*
                foreach ($existingSubsByType as $subToDelete) {
                    $product->removeSubscriptionType($subToDelete);
                    $this->entityManager->remove($subToDelete);
                }
                */
            }
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    private function autoTranslate(string $name, string $desc, string $from): array
    {
        $to = $from === 'fr' ? 'en' : 'fr';

        return [
            $from => ['name' => $name, 'description' => $desc],
            $to => [
                'name' => $this->translator->translate($name, $from, $to),
                'description' => $this->translator->translate($desc, $from, $to),
            ]
        ];
    }
}

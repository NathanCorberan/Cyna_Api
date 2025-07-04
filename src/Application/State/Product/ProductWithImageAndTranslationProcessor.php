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
use App\Entity\SubscriptionType;
use App\Service\AzureTranslateService;
use App\Service\StripeProductManager;

class ProductWithImageAndTranslationProcessor implements ProcessorInterface
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
        $request = $this->requestStack->getCurrentRequest();

        $name = $request->request->get('name');
        $description = $request->request->get('description');
        $lang = $request->request->get('lang', 'fr');
        $status = $request->request->get('status', 'Disponible');
        $stock = (int) $request->request->get('available_stock', 0);
        $categoryId = $request->request->get('category_id');

        /** @var UploadedFile|array|null $imageFiles */
        $imageFiles = $request->files->get('imageFile');

        $product = new Product();
        $product->setStatus($status);
        $product->setAvailableStock($stock);
        $product->setCreationDate(date('Y-m-d H:i:s'));

        if ($categoryId) {
            $category = $this->entityManager->getRepository(Category::class)->find($categoryId);
            if ($category) {
                $product->setCategory($category);
            }
        }

        // Gestion des images — supporte tableau ou fichier unique
        if (!$imageFiles) {
            $imageFiles = [];
        } elseif (!is_array($imageFiles)) {
            $imageFiles = [$imageFiles];
        }

        $uploadDir = $this->projectDir . '/public/assets/images/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($imageFiles as $imageFile) {
            if (!$imageFile instanceof UploadedFile) {
                continue;
            }
            $originalName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $originalName);
            $filename = $safeName . '_' . uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move($uploadDir, $filename);

            $productImage = new ProductImage();
            $productImage->setName($safeName);
            $productImage->setImageLink($filename);
            $productImage->setProduct($product);
            $this->entityManager->persist($productImage);

            $product->addProductImage($productImage);
        }

        // Traductions
        $translations = $this->autoTranslate($name, $description, $lang);
        foreach ($translations as $code => $traduction) {
            $productLang = new ProductLangage();
            $productLang->setProduct($product);
            $productLang->setCode($code);
            $productLang->setName($traduction['name']);
            $productLang->setDescription($traduction['description']);
            $this->entityManager->persist($productLang);
            $product->addProductLangage($productLang);
        }

        // --- Création du produit Stripe (UN SEUL produit pour toutes les subscriptions)
        $stripeProductId = $this->stripeProductManager->createStripeProduct(
            $product,
            $name ?? 'Produit',
            $description ?? null
        );
        $product->setStripeProductId($stripeProductId);

        // Gestion subscriptions : attend une chaîne JSON unique
        $subscriptionsJson = $request->request->get('subscriptionTypes');
        if ($subscriptionsJson) {
            $subscriptionsData = json_decode($subscriptionsJson, true);
            if (is_array($subscriptionsData)) {
                foreach ($subscriptionsData as $data) {
                    if (empty($data['type']) || empty($data['price'])) {
                        continue;
                    }

                    $subscription = new SubscriptionType();
                    $subscription->setType($data['type']);
                    $subscription->setPrice($data['price']);
                    $subscription->setProduct($product);

                    $stripePriceId = $this->stripeProductManager->createPriceForProduct(
                        $stripeProductId,
                        $data['price'],
                        $data['type']
                    );
                    $subscription->setStripePriceId($stripePriceId);

                    $this->entityManager->persist($subscription);
                    $product->addSubscriptionType($subscription);
                }
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
            ],
        ];
    }
}
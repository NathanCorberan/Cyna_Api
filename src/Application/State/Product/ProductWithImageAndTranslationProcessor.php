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

class ProductWithImageAndTranslationProcessor implements ProcessorInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private string $projectDir,
        private AzureTranslateService $translator
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
        /** @var UploadedFile|null $imageFile */
        $imageFile = $request->files->get('imageFile');

        $product = new Product();
        $product->setNameForLocale($lang); // setter custom si utile
        $product->setStatus($status);
        $product->setAvailableStock($stock);
        $product->setCreationDate(date('Y-m-d H:i:s'));

        // Lien à la catégorie
        if ($categoryId) {
            $category = $this->entityManager->getRepository(Category::class)->find($categoryId);
            if ($category) {
                $product->setCategory($category);
            }
        }

        // Image
        if ($imageFile) {
            $uploadDir = $this->projectDir . '/public/assets/images/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
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

        // Traduction FR <-> EN
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

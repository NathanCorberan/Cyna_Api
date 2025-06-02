<?php

namespace App\Application\State\Category;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\Category;
use App\Entity\CategoryImage;
use App\Entity\CategoryLanguage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\AzureTranslateService;

class CategoryWithImageAndTranslationProcessor implements ProcessorInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private string $projectDir,
        private AzureTranslateService $translator // ✅ OBLIGATOIRE pour l’autoTranslate
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $request = $this->requestStack->getCurrentRequest();

        $name = $request->request->get('name');
        $description = $request->request->get('description');
        $lang = $request->request->get('lang', 'fr'); // défaut fr
        $categoryOrder = $request->request->get('category_order');
        /** @var UploadedFile|null $imageFile */
        $imageFile = $request->files->get('imageFile');

        // 1. Création de la catégorie
        $category = new Category();
        $category->setName($name); // pour rétrocompatibilité
        $category->setCategoryOrder((int) $categoryOrder);
        $category->setCreationDate(date('Y-m-d H:i:s'));

        // 2. Gestion de l’image (si présente)
        if ($imageFile) {
            $uploadDir = $this->projectDir . '/public/assets/images/categories/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $originalName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $originalName);
            $filename = $safeName . '_' . uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move($uploadDir, $filename);

            $categoryImage = new CategoryImage();
            $categoryImage->setName($safeName);
            $categoryImage->setImageLink('/assets/images/categories/' . $filename);
            $categoryImage->setCategory($category);
            $this->entityManager->persist($categoryImage);

            $category->addCategoryImage($categoryImage);
        }

        // 3. Traduction automatique Azure
        $translations = $this->autoTranslate($name, $description, $lang);

        // 4. Création des entités CategoryLanguage (fr + en)
        foreach ($translations as $code => $traduction) {
            $catLang = new CategoryLanguage();
            $catLang->setCategoryId($category);
            $catLang->setCode($code);
            $catLang->setName($traduction['name']);
            $catLang->setDescription($traduction['description']);
            $this->entityManager->persist($catLang);

            $category->addCategoryLanguage($catLang);
        }

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

    /**
     * Traduction automatique FR <-> EN via Azure Translate
     */
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

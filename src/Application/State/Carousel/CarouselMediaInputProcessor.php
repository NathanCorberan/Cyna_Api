<?php

namespace App\Application\State\Carousel;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\Carousel;
use App\Entity\CarouselLangage;
use App\Service\AzureTranslateService;

class CarouselMediaInputProcessor implements ProcessorInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private string $projectDir,
        private AzureTranslateService $translator, // 👈 injection du traducteur
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request->isMethod('POST')) {
            throw new \RuntimeException('Only POST method is supported.');
        }

        $imageFile = $request->files->get('imageFile');
        $panelOrder = $request->request->get('panel_order');
        $title = $request->request->get('title');
        $description = $request->request->get('description');
        $lang = $request->request->get('code') ?? 'fr';

        $uploadDir = $this->projectDir . '/public/assets/images/carousel/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $carousel = new Carousel();
        if ($panelOrder !== null) {
            $carousel->setPanelOrder((int)$panelOrder);
        }

        if ($imageFile) {
            $extension = $imageFile->guessExtension() ?? 'jpg';
            $filename = 'proxy-image_' . uniqid() . '.' . $extension;
            $imageFile->move($uploadDir, $filename);
            $carousel->setImageLink($filename);
        }

        // === Traductions automatiques
        $translations = $this->autoTranslate($title, $description, $lang);
        foreach ($translations as $code => $t) {
            $carouselLangage = new CarouselLangage();
            $carouselLangage->setTitle($t['title']);
            $carouselLangage->setDescription($t['description']);
            $carouselLangage->setCode($code);
            $carouselLangage->setCarousel($carousel);
            $this->entityManager->persist($carouselLangage);
        }

        $this->entityManager->persist($carousel);
        $this->entityManager->flush();

        return $carousel;
    }

    private function autoTranslate(string $title, string $desc, string $from): array
    {
        $to = $from === 'fr' ? 'en' : 'fr';

        return [
            $from => [
                'title' => $title,
                'description' => $desc
            ],
            $to => [
                'title' => $this->translator->translate($title, $from, $to),
                'description' => $this->translator->translate($desc, $from, $to),
            ]
        ];
    }
}

<?php
namespace App\Application\State\Carousel;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\Carousel;

class CarouselMediaInputProcessor implements ProcessorInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private string $projectDir, // <- injection du chemin projet Symfony
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $request = $this->requestStack->getCurrentRequest();

        $imageFile = $request->files->get('imageFile');
        $panelOrder = $request->request->get('panel_order');

        $uploadDir = $this->projectDir . '/public/assets/images/carousel/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $carousel = new Carousel();
        $carousel->setPanelOrder((int)$panelOrder);

        if ($imageFile) {
            $filename = uniqid('carousel_', true) . '.' . $imageFile->guessExtension();
            $imageFile->move($uploadDir, $filename);
            $carousel->setImageLink('/assets/images/carousel/' . $filename);
        }

        $this->entityManager->persist($carousel);
        $this->entityManager->flush();

        return $carousel;
    }
}

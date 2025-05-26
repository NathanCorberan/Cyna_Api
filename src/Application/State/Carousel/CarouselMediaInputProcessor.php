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
        private string $projectDir,
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

        // === Trouver l'entité existante pour PUT (update)
        $carousel = null;
        if (
            isset($uriVariables['id']) &&
            $request->isMethod('PUT')
        ) {
            $carousel = $this->entityManager->getRepository(Carousel::class)->find($uriVariables['id']);
            if (!$carousel) {
                throw new \Exception('Carousel not found');
            }
        }
        // === Création sinon (POST)
        if (!$carousel) {
            $carousel = new Carousel();
        }

        // Update panel_order si fourni
        if ($panelOrder !== null) {
            $carousel->setPanelOrder((int)$panelOrder);
        }

        // Si une image est envoyée, supprime l'ancienne (update), puis upload la nouvelle
        if ($imageFile) {
            // Supprimer l'ancien fichier
            $oldImage = $carousel->getImageLink();
            if ($oldImage) {
                $oldPath = $this->projectDir . '/public' . $oldImage;
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            // Nom du fichier propre
            $originalName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $originalName);
            $filename = $safeName . '_' . uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move($uploadDir, $filename);
            $carousel->setImageLink('/assets/images/carousel/' . $filename);
        }

        $this->entityManager->persist($carousel);
        $this->entityManager->flush();

        return $carousel;
    }
}

<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\CarouselLangage;
use App\Entity\Carousel;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class CarouselLangageDataPersister implements ProcessorInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof CarouselLangage) {
            return $data;
        }

        // ✅ Vérifier que `carousel_id` est bien envoyé
        if ($data->getCarouselId() !== null) {
            $carousel = $this->entityManager->getRepository(Carousel::class)->find($data->getCarouselId());

            if (!$carousel) {
                throw new BadRequestException("Le carousel avec l'ID {$data->getCarouselId()} n'existe pas.");
            }

            $data->setCarousel($carousel); // ✅ Associer le carousel
            $data->setCarouselId(null); // ✅ Supprimer `carousel_id` après l'association
        }

        // ✅ Enregistrer en base
        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}

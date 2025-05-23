<?php

namespace App\Entity;

use App\Repository\CarouselLangageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Application\State\Carousel\CarouselLangageDataPersister;

#[ORM\Entity(repositoryClass: CarouselLangageRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['carousel_langage:read']],
    denormalizationContext: ['groups' => ['carousel_langage:write']],
    processor: CarouselLangageDataPersister::class,
    security: "is_granted('ROLE_ADMIN')"
)]
class CarouselLangage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['carousel_langage:read', 'carousel:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 2)]
    #[Groups(['carousel_langage:read', 'carousel_langage:write', 'carousel:read'])]
    private ?string $code = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['carousel_langage:read', 'carousel_langage:write', 'carousel:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['carousel_langage:read', 'carousel_langage:write', 'carousel:read'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'carouselLangages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['carousel_langage:read'])]
    private ?Carousel $carousel = null;

    #[Groups(['carousel_langage:write'])]
    private ?int $carousel_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getCarousel(): ?Carousel
    {
        return $this->carousel;
    }

    public function setCarousel(?Carousel $carousel): static
    {
        $this->carousel = $carousel;
        return $this;
    }

    public function getCarouselId(): ?int
    {
        return $this->carousel_id;
    }

    public function setCarouselId(?int $carousel_id): static
    {
        $this->carousel_id = $carousel_id;
        return $this;
    }
}

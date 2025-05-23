<?php

namespace App\Entity;

use App\Repository\ProductImageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Application\State\Product\ProductImageDataPersister;

#[ORM\Entity(repositoryClass: ProductImageRepository::class)]
#[ApiResource(    
    normalizationContext: ['groups' => ['ProductImage:read']],
    denormalizationContext: ['groups' => ['ProductImage:write']],
    processor: ProductImageDataPersister::class,
    security: "is_granted('ROLE_ADMIN')",
)]  // Ajoute cette annotation pour exposer l'entité dans l'API
class ProductImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['ProductImage:read','ProductImage:write'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['ProductImage:read','ProductImage:write'])]
    private ?string $image_link = null;

    #[ORM\Column(length: 50)]
    #[Groups(['ProductImage:read','ProductImage:write'])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'productImages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['ProductImage:read'])]
    private ?Product $product = null;

    #[Groups(['ProductImage:write'])] // ✅ Écriture uniquement
    private ?int $product_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImageLink(): ?string
    {
        return $this->image_link;
    }

    public function setImageLink(string $image_link): static
    {
        $this->image_link = $image_link;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getProduct(): ?product
    {
        return $this->product;
    }

    public function setProduct(?product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getProductId(): ?int
    {
        return $this->product_id;
    }

    public function setProductId(?int $product_id): static // ✅ Correction du setter (avant `setProducId`)
    {
        $this->product_id = $product_id;
        return $this;
    }
}

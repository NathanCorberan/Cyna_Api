<?php

namespace App\Entity;

use App\Repository\ProductLangageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Application\State\Product\ProductLangageDataPersister;

#[ORM\Entity(repositoryClass: ProductLangageRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['ProductLangage:read']],
    denormalizationContext: ['groups' => ['ProductLangage:write']],
    processor: ProductLangageDataPersister::class,
    security: "is_granted('ROLE_ADMIN')",
)]  // Ajoute cette annotation pour exposer l'entité dans l'API
class ProductLangage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['ProductLangage:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 2)]
    #[Groups(['ProductLangage:write','ProductLangage:read'])]
    private ?string $code = null;

    #[ORM\Column(length: 50)]
    #[Groups(['ProductLangage:write','ProductLangage:read'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['ProductLangage:write','ProductLangage:read'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'productLangages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['ProductLangage:read'])]
    private ?Product $product = null;

    #[Groups(['ProductLangage:write','ProductLangage:read'])]
    private ?int $product_id = null;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
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

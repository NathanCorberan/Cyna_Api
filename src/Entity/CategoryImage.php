<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Application\State\Category\CategoryImageDataPersister;
use Doctrine\DBAL\Types\Types;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource(
    normalizationContext: ['groups' => ['category_image:read']],
    denormalizationContext: ['groups' => ['category_image:write']],
    processor: CategoryImageDataPersister::class,
    security: "is_granted('ROLE_ADMIN')",
)]
class CategoryImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['category_image:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['category_image:read', 'category_image:write'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['category_image:read', 'category_image:write'])]
    private ?string $image_link = null;

    #[ORM\ManyToOne(inversedBy: 'categoryImages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['category_image:read'])]
    private ?Category $category = null;

    // 🆕 Champ temporaire pour recevoir l'ID de la catégorie
    #[Groups(['category_image:write'])]
    private ?int $category_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getCategoryId(): ?int
    {
        return $this->category_id;
    }

    public function setCategoryId(?int $category_id): static
    {
        $this->category_id = $category_id;
        return $this;
    }
}

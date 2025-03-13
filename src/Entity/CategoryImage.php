<?php

namespace App\Entity;

use App\Repository\CategoryImageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use App\State\CategoryImageDataPersister;

#[ORM\Entity(repositoryClass: CategoryImageRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['category_image:read']],
    denormalizationContext: ['groups' => ['category_image:write']],
    processor: CategoryImageDataPersister::class
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

    #[Groups(['category_image:write'])]
    private ?string $category_name = null;

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

    public function getCategoryName(): ?string
    {
        return $this->category_name;
    }

    public function setCategoryName(?string $category_name): static
    {
        $this->category_name = $category_name;
        return $this;
    }
}

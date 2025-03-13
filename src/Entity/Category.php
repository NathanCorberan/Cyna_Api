<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
#[ApiResource(
    normalizationContext: ['groups' => ['category:read']],
    denormalizationContext: ['groups' => ['category:write']],
)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['category:read', 'category:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['category:read', 'category:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    #[Groups(['category:read', 'category:write'])]
    private ?string $creation_date = null;

    #[ORM\Column]
    #[Groups(['category:read', 'category:write'])]
    private ?int $category_order = null;

    #[ORM\OneToMany(targetEntity: CategoryImage::class, mappedBy: 'category')]
    private Collection $categoryImages;

    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'category')]
    private Collection $products;

    public function __construct()
    {
        $this->categoryImages = new ArrayCollection();
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreationDate(): ?string
    {
        return $this->creation_date;
    }

    public function setCreationDate(string $creation_date): static
    {
        $this->creation_date = $creation_date;
        return $this;
    }

    public function getCategoryOrder(): ?int
    {
        return $this->category_order;
    }

    public function setCategoryOrder(int $category_order): static
    {
        $this->category_order = $category_order;
        return $this;
    }

    /**
     * @return Collection<int, CategoryImage>
     */
    public function getCategoryImages(): Collection
    {
        return $this->categoryImages;
    }

    public function addCategoryImage(CategoryImage $categoryImage): static
    {
        if (!$this->categoryImages->contains($categoryImage)) {
            $this->categoryImages->add($categoryImage);
            $categoryImage->setCategory($this);
        }

        return $this;
    }

    public function removeCategoryImage(CategoryImage $categoryImage): static
    {
        if ($this->categoryImages->removeElement($categoryImage)) {
            if ($categoryImage->getCategory() === $this) {
                $categoryImage->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setCategory($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            if ($product->getCategory() === $this) {
                $product->setCategory(null);
            }
        }

        return $this;
    }

    // ✅ Retourne l'image principale de la catégorie
    #[Groups(['category:read'])]
    public function getImageLink(): ?string
    {
        foreach ($this->categoryImages as $image) {
            return $image->getImageLink(); // Retourne l'URL de la première image trouvée
        }
        return null;
    }
}

<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: '`category`', uniqueConstraints: [
    new ORM\UniqueConstraint(name: "UNIQ_CATEGORY_ORDER", columns: ["category_order"]) 
])]
#[ApiResource]  // Ajoute cette annotation pour exposer l'entité dans l'API
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $creation_date = null;

    #[ORM\Column]
    private ?int $category_order = null;

    /**
     * @var Collection<int, CategoryImage>
     */
    #[ORM\OneToMany(targetEntity: CategoryImage::class, mappedBy: 'category')]
    private Collection $categoryImages;

    /**
     * @var Collection<int, Product>
     */
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
            // set the owning side to null (unless already changed)
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
            // set the owning side to null (unless already changed)
            if ($product->getCategory() === $this) {
                $product->setCategory(null);
            }
        }

        return $this;
    }
}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\CategoryRepository;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;

use App\Entity\Product;
use App\Application\State\Category\CategoryProductsProvider;
use App\Dto\Product\ProductDetailsOutputDto;
use App\Application\State\Category\CategoryWithImageAndTranslationProcessor;
use App\Application\State\Category\CategoryCollectionProvider;
use App\Application\State\Category\CategoryCascadeDeleteProcessor;



#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['category:read']],
    denormalizationContext: ['groups' => ['category:write']],
    order: ['category_order' => 'ASC'],
    operations: [
        new GetCollection(
            provider: CategoryCollectionProvider::class
        ),
        new Get(),
        new Post(
            processor: CategoryWithImageAndTranslationProcessor::class,
            security: "is_granted('ROLE_ADMIN')",
            input: false,
            inputFormats: ['multipart' => ['multipart/form-data']],
        ),
        new Patch(security: "is_granted('ROLE_ADMIN')"), // 🔒 Admin seulement
        new Delete(
            processor: CategoryCascadeDeleteProcessor::class,
            security: "is_granted('ROLE_ADMIN')"
        ),
        new GetCollection(
            uriTemplate: '/categorie/{id}/products',
            name: 'get_category_products',
            provider: CategoryProductsProvider::class,
            status: 200,
            output: ProductDetailsOutputDto::class,
            normalizationContext: ['groups' => ['Product:read']]
        )



    ]
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

    /**
     * @var Collection<int, CategoryLanguage>
     */
    #[ORM\OneToMany(targetEntity: CategoryLanguage::class, mappedBy: 'categoryId')]
    #[Groups(['category:read'])]
    private Collection $categoryLanguages;

    public function __construct()
    {
        $this->categoryImages = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->categoryLanguages = new ArrayCollection();
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
            return $image->getImageLink();
        }
        return null;
    }

    /**
     * @return Collection<int, CategoryLanguage>
     */
    public function getCategoryLanguages(): Collection
    {
        return $this->categoryLanguages;
    }

    public function addCategoryLanguage(CategoryLanguage $categoryLanguage): static
    {
        if (!$this->categoryLanguages->contains($categoryLanguage)) {
            $this->categoryLanguages->add($categoryLanguage);
            $categoryLanguage->setCategoryId($this);
        }

        return $this;
    }

    public function removeCategoryLanguage(CategoryLanguage $categoryLanguage): static
    {
        if ($this->categoryLanguages->removeElement($categoryLanguage)) {
            // set the owning side to null (unless already changed)
            if ($categoryLanguage->getCategoryId() === $this) {
                $categoryLanguage->setCategoryId(null);
            }
        }

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;

//nique
use Symfony\Component\Validator\Constraints as Assert;
use App\Application\State\Product\ProductDataPersister;
use App\Application\State\Product\ProductProvider;
use App\Application\State\Product\ProductItemProvider;
use App\Application\State\Product\TopOrdersProductProvider;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['Product:read']],
    denormalizationContext: ['groups' => ['Product:write']],
    operations: [
        new GetCollection(security: "is_granted('PUBLIC_ACCESS')", provider: ProductProvider::class),
        new Get(security: "is_granted('PUBLIC_ACCESS')", provider: ProductItemProvider::class),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')", uriTemplate: '/products/{id}'),
        new Delete(security: "is_granted('ROLE_ADMIN')", uriTemplate: '/products/{id}'), # ⛔️ PAS de processor ici
        new Get(
            uriTemplate: '/top/products',
            name: 'products_top',
            provider: TopOrdersProductProvider::class,
            security: "is_granted('PUBLIC_ACCESS')"
        )
    ],
    security: "is_granted('ROLE_ADMIN')"
)]

class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['Product:read'])]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['Product:read', 'Product:write'])]
    private ?int $available_stock = null;

    #[ORM\Column(length: 50)]
    #[Groups(['Product:read', 'Product:write'])]
    private ?string $creation_date = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ["Disponible", "Indisponible"], message: "Le statut doit être 'Disponible' ou 'Indisponible'.")]
    #[Groups(['Product:read', 'Product:write'])]
    private ?string $status = null;

    #[Groups(['Product:write'])]
    private ?int $category_id = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['Product:read'])]
    private ?Category $category = null;

    /**
     * @var Collection<int, ProductLangage>
     */
    #[ORM\OneToMany(targetEntity: ProductLangage::class, mappedBy: 'product')]
    #[Groups(['Product:read'])]
    private Collection $productLangages;

    /**
     * @var Collection<int, ProductImage>
     */
    #[ORM\OneToMany(targetEntity: ProductImage::class, mappedBy: 'product')]
    #[Groups(['Product:read'])]
    private Collection $productImages;

    /**
     * @var Collection<int, SubscriptionType>
     */
    #[ORM\OneToMany(targetEntity: SubscriptionType::class, mappedBy: 'product')]
    #[Groups(['Product:read'])]
    private Collection $subscriptionTypes;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'product')]
    private Collection $orderItems;

    public function __construct()
    {
        $this->productLangages = new ArrayCollection();
        $this->productImages = new ArrayCollection();
        $this->subscriptionTypes = new ArrayCollection();
        $this->orderItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAvailableStock(): ?int
    {
        return $this->available_stock;
    }

    public function setAvailableStock(?int $available_stock): static
    {
        $this->available_stock = $available_stock;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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

    /**
     * @return Collection<int, ProductLangage>
     */
    public function getProductLangages(): Collection
    {
        return $this->productLangages;
    }

    public function addProductLangage(ProductLangage $productLangage): static
    {
        if (!$this->productLangages->contains($productLangage)) {
            $this->productLangages->add($productLangage);
            $productLangage->setProduct($this);
        }

        return $this;
    }

    public function removeProductLangage(ProductLangage $productLangage): static
    {
        if ($this->productLangages->removeElement($productLangage)) {
            if ($productLangage->getProduct() === $this) {
                $productLangage->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductImage>
     */
    public function getProductImages(): Collection
    {
        return $this->productImages;
    }

    public function addProductImage(ProductImage $productImage): static
    {
        if (!$this->productImages->contains($productImage)) {
            $this->productImages->add($productImage);
            $productImage->setProduct($this);
        }

        return $this;
    }

    public function removeProductImage(ProductImage $productImage): static
    {
        if ($this->productImages->removeElement($productImage)) {
            if ($productImage->getProduct() === $this) {
                $productImage->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SubscriptionType>
     */
    public function getSubscriptionTypes(): Collection
    {
        return $this->subscriptionTypes;
    }

    public function addSubscriptionType(SubscriptionType $subscriptionType): static
    {
        if (!$this->subscriptionTypes->contains($subscriptionType)) {
            $this->subscriptionTypes->add($subscriptionType);
            $subscriptionType->setProduct($this);
        }

        return $this;
    }

    public function removeSubscriptionType(SubscriptionType $subscriptionType): static
    {
        if ($this->subscriptionTypes->removeElement($subscriptionType)) {
            if ($subscriptionType->getProduct() === $this) {
                $subscriptionType->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setProduct($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            if ($orderItem->getProduct() === $this) {
                $orderItem->setProduct(null);
            }
        }

        return $this;
    }

    public function getNameForLocale(string $locale = 'fr'): ?string
    {
        foreach ($this->getProductLangages() as $langage) {
            if ($langage->getCode() === $locale) {
                return $langage->getName();
            }
        }
        // Fallback: retourne le premier nom dispo s'il n'y a pas la langue demandée
        if (!$this->getProductLangages()->isEmpty()) {
            return $this->getProductLangages()->first()->getName();
        }
        return null;
    }

}

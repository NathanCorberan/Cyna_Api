<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ApiResource]  // Ajoute cette annotation pour exposer l'entité dans l'API
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $available_stock = null;

    #[ORM\Column(length: 50)]
    private ?string $creation_date = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    /**
     * @var Collection<int, ProductLangage>
     */
    #[ORM\OneToMany(targetEntity: ProductLangage::class, mappedBy: 'product')]
    private Collection $productLangages;

    /**
     * @var Collection<int, ProductImage>
     */
    #[ORM\OneToMany(targetEntity: ProductImage::class, mappedBy: 'product')]
    private Collection $productImages;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\ManyToMany(targetEntity: Order::class, mappedBy: 'products')]
    private Collection $orders;

    /**
     * @var Collection<int, SubscriptionType>
     */
    #[ORM\OneToMany(targetEntity: SubscriptionType::class, mappedBy: 'product')]
    private Collection $subscriptionTypes;

    public function __construct()
    {
        $this->productLangages = new ArrayCollection();
        $this->productImages = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->subscriptionTypes = new ArrayCollection();
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
            // set the owning side to null (unless already changed)
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
            // set the owning side to null (unless already changed)
            if ($productImage->getProduct() === $this) {
                $productImage->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->addProduct($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            $order->removeProduct($this);
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
            // set the owning side to null (unless already changed)
            if ($subscriptionType->getProduct() === $this) {
                $subscriptionType->setProduct(null);
            }
        }

        return $this;
    }
}

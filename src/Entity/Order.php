<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Application\State\Order\OrderDataPersister;
use App\Dto\Cart\CreateCartInputDto;
use App\Application\State\Cart\CreateCartProcessor;
use App\Application\State\Checkout\CheckoutState;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ApiResource(
    normalizationContext: ['groups' => ['Order:read']],
    denormalizationContext: ['groups' => ['Order:write']],
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            uriTemplate: '/orders',
            input: CreateCartInputDto::class,
            output: Order::class,
            processor: CreateCartProcessor::class,
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        new Patch(),
        new Delete(),
        new Post(
            uriTemplate: '/checkout',
            input: false,
            output: Order::class,
            processor: CheckoutState::class,
            security: "is_granted('IS_AUTHENTICATED_FULLY')"
        ),
    ],
)]
class Order
{
    // ✅ STATUSES valides (PHP side)
    //public static array $allowedStatuses = ['cart', 'payed', 'refunded'];
    public static array $allowedStatuses = ['cart', 'payed'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['Order:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['Order:read', 'Order:write'])]
    private ?string $order_date = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['Order:read', 'Order:write'])]
    private ?string $total_amount = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: true)] // optionnel
    #[Groups(['Order:read'])]
    private ?User $user = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['Order:read'])]
    private Collection $orderItems;

    #[Groups(['Order:write'])]
    #[SerializedName('user_id')]
    private ?int $user_id = null;

    #[Groups(['Order:write'])]
    #[SerializedName('orderItems_id')]
    private array $orderItems_id = [];

    // ✅ CHAMP ENUM SQL (natif en BDD)
    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'cart'], columnDefinition: "ENUM('cart', 'payed')")]
    #[Groups(['Order:read', 'Order:write'])]
    private ?string $status = 'cart';

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    #[Groups(['Order:read', 'Order:write', 'OrderItem:read'])]
    private ?string $cartToken = null;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderDate(): ?string
    {
        return $this->order_date;
    }

    public function setOrderDate(string $order_date): static
    {
        $this->order_date = $order_date;
        return $this;
    }

    public function getTotalAmount(): ?string
    {
        return $this->total_amount;
    }

    public function setTotalAmount(string $total_amount): static
    {
        $this->total_amount = $total_amount;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
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
            $orderItem->setOrder($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            if ($orderItem->getOrder() === $this) {
                $orderItem->setOrder(null);
            }
        }

        return $this;
    }

    public function recalculateTotalAmount(): void
    {
        $total = "0.00";
        foreach ($this->orderItems as $item) {
            $price = $item->getTotalPrice() ?? "0.00";
            $total = bcadd($total, $price, 2);
        }
        $this->total_amount = $total;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(?int $user_id): static
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function getOrderItemsId(): array
    {
        return $this->orderItems_id;
    }

    public function setOrderItemsId(array $orderItems_id): static
    {
        $this->orderItems_id = $orderItems_id;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        if (!in_array($status, self::$allowedStatuses, true)) {
            throw new \InvalidArgumentException("Invalid order status: $status");
        }

        $this->status = $status;
        return $this;
    }

    public function getCartToken(): ?string
    {
        return $this->cartToken;
    }

    public function setCartToken(?string $cartToken): static
    {
        $this->cartToken = $cartToken;
        return $this;
    }
}

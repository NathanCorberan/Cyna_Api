<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use App\State\OrderItemDataPersister;
use App\Dto\Order\OrderItemInputDto;
use App\Dto\Order\OrderItemPatchInputDto;
use App\Application\State\Cart\CartProvider;
use App\Application\State\Order\OrderItem\IncrementOrderItemQuantityProcessor;
use App\Application\State\Order\OrderItem\DecrementOrderItemQuantityProcessor;
use ApiPlatform\Metadata\Delete as DeleteOperation;
use App\State\SecureOrderItemDeletionProcessor;
use App\State\OrderItemPatchState;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ArrayObject;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['OrderItem:read']],
    denormalizationContext: ['groups' => ['OrderItem:write']],
    operations: [
        new GetCollection(
            openapi: new Operation(
                summary: 'Lister tous les OrderItem',
                description: 'Récupère la liste complète des éléments de commande (OrderItem) enregistrés.'
            )
        ),
        new Get(
            openapi: new Operation(
                summary: 'Récupérer un OrderItem',
                description: 'Récupère les détails d’un OrderItem spécifique via son identifiant.'
            )
        ),
        new Post(
            input: OrderItemInputDto::class,
            output: OrderItem::class,
            processor: OrderItemDataPersister::class,
            security: "is_granted('PUBLIC_ACCESS')",
            openapi: new Operation(
                summary: 'Créer un OrderItem',
                description: 'Crée un nouvel élément de commande (OrderItem) à partir d’un produit et d’un panier ou d’une commande.'
            )
        ),
        new Patch(
            input: OrderItemPatchInputDto::class,
            processor: OrderItemPatchState::class,
            security: "is_granted('PUBLIC_ACCESS')",
            openapi: new Operation(
                summary: 'Modifier un OrderItem',
                description: 'Modifie les propriétés d’un OrderItem existant, comme la quantité ou le produit associé.'
            )
        ),
        new DeleteOperation(
            processor: SecureOrderItemDeletionProcessor::class,
            security: "is_granted('PUBLIC_ACCESS')",
            openapi: new Operation(
                summary: 'Supprimer un OrderItem',
                description: 'Supprime un OrderItem spécifique, avec des vérifications de sécurité.'
            )
        ),
        new GetCollection(
            name: 'get_cart_items',
            uriTemplate: '/cart',
            provider: CartProvider::class,
            normalizationContext: ['groups' => ['OrderItem:read']],
            security: "is_granted('PUBLIC_ACCESS')",
            openapi: new Operation(
                summary: 'Afficher les OrderItem du panier',
                description: 'Retourne la liste des éléments de commande liés au panier actif de l’utilisateur.'
            )
        ),
        new Patch(
            uriTemplate: '/order_items/{id}/increment',
            processor: IncrementOrderItemQuantityProcessor::class,
            name: 'order_item_increment',
            input: false, // ⬅️ DIT À API PLATFORM DE NE RIEN DÉSÉRIALISER
            openapi: new Operation(
                summary: 'Incrémenter la quantité d’un OrderItem',
                description: 'Augmente la quantité d’un OrderItem de +1 et recalcule automatiquement le total_price.',
                requestBody: new RequestBody(
                    description: 'Aucun contenu requis',
                    required: false,
                    content: new \ArrayObject([])
                ),
                tags: ['OrderItem']
            )
            ),
            new Patch(
                uriTemplate: '/order_items/{id}/decrement',
                processor: DecrementOrderItemQuantityProcessor::class,
                name: 'order_item_decrement',
                input: false,
                openapi: new Operation(
                    summary: 'Décrémenter la quantité d’un OrderItem',
                    description: 'Diminue la quantité d’un OrderItem de 1 si elle est supérieure à 1, puis recalcule le total_price.',
                    requestBody: new RequestBody(
                        description: 'Aucun contenu requis',
                        required: false,
                        content: new \ArrayObject([])
                    ),
                    tags: ['OrderItem']
                )
            ),

    ]
)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['OrderItem:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['OrderItem:read'])]
    private ?Order $order = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['OrderItem:read'])]
    private ?Product $product = null;

    #[ORM\Column]
    #[Groups(['OrderItem:read', 'OrderItem:write'])]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['OrderItem:read', 'OrderItem:write'])]
    private ?string $unitPrice = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['OrderItem:read'])]
    private ?string $total_price = null;

    #[Groups(['OrderItem:write'])]
    #[SerializedName("order_id")]
    private ?int $order_id = null;

    #[Groups(['OrderItem:write'])]
    #[SerializedName("product_id")]
    private ?int $product_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;
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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        $this->updateTotalPrice();
        return $this;
    }

    public function getUnitPrice(): ?string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string $unitPrice): static
    {
        $this->unitPrice = str_replace(',', '.', $unitPrice); // permet "9,99" depuis JSON
        $this->updateTotalPrice();
        return $this;
    }

    public function getTotalPrice(): ?string
    {
        return $this->total_price;
    }

    public function setTotalPrice(string $total_price): static
    {
        $this->total_price = $total_price;
        return $this;
    }

    public function getOrderId(): ?int
    {
        return $this->order_id;
    }

    public function setOrderId(?int $order_id): static
    {
        $this->order_id = $order_id;
        return $this;
    }

    public function getProductId(): ?int
    {
        return $this->product_id;
    }

    public function setProductId(?int $product_id): static
    {
        $this->product_id = $product_id;
        return $this;
    }

    private function updateTotalPrice(): void
    {
        if ($this->unitPrice !== null && $this->quantity !== null) {
            $this->total_price = bcmul($this->unitPrice, (string) $this->quantity, 2);
        }
    }
}

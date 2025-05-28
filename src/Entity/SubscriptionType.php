<?php

namespace App\Entity;

use App\Repository\SubscriptionTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Application\State\Subscription\SubscriptionTypeDataPersister;

#[ORM\Entity(repositoryClass: SubscriptionTypeRepository::class)]
#[ApiResource(    
    normalizationContext: ['groups' => ['SubscriptionType:read']],
    denormalizationContext: ['groups' => ['SubscriptionType:write']],
    processor: SubscriptionTypeDataPersister::class,
    security: "is_granted('ROLE_ADMIN')",
)]
class SubscriptionType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['SubscriptionType:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['SubscriptionType:write', 'SubscriptionType:read'])]
    private ?string $type = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    #[Groups(['SubscriptionType:write', 'SubscriptionType:read'])]
    private ?string $price = null;

    #[ORM\ManyToOne(inversedBy: 'subscriptionTypes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['SubscriptionType:read'])] // ✅ Lecture seule
    private ?Product $product = null;

    #[Groups(['SubscriptionType:write'])] // ✅ Écriture uniquement
    private ?int $product_id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private $stripePriceId;

    /**
     * @var Collection<int, Subscription>
     */
    #[ORM\OneToMany(targetEntity: Subscription::class, mappedBy: 'subscriptionType')]
    private Collection $subscriptions;

    public function __construct()
    {
        $this->subscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;
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

    public function getStripePriceId(): ?string 
    { 
        return $this->stripePriceId; 
    }
    public function setStripePriceId(?string $id): self 
    { 
        $this->stripePriceId = $id; return $this; 
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(Subscription $subscription): static
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions->add($subscription);
            $subscription->setSubscriptionType($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): static
    {
        if ($this->subscriptions->removeElement($subscription)) {
            if ($subscription->getSubscriptionType() === $this) {
                $subscription->setSubscriptionType(null);
            }
        }

        return $this;
    }
}

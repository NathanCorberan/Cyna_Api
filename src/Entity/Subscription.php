<?php

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\SecurityBundle\Security;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\Metadata\Delete as DeleteOperation;
use ArrayObject;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Dto\Subscription\MySubOutputDto;
use App\Application\State\Subscription\MySubProvider;


#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
#[ApiResource(
    security: "is_granted('ROLE_ADMIN')",
    normalizationContext: ['groups' => ['Subscription:read']],
    denormalizationContext: ['groups' => ['Subscription:write']],
    operations: [
        new GetCollection(
            openapi: new Operation(
                summary: 'Lister toutes les abonnements',
                description: 'Récupère la liste complète des abonnements (Subscription) enregistrés.',
                tags: ['Subscription']
            )
        ),
        new Get(
            openapi: new Operation(
                summary: 'Récupérer un abonnement',
                description: 'Récupère les détails d’un abonnement (Subscription) spécifique via son identifiant.',
                tags: ['Subscription']
            )
        ),
        new GetCollection(
            uriTemplate: '/my-sub',
            output: MySubOutputDto::class,
            provider: MySubProvider::class,
            normalizationContext: ['groups' => ['my_sub:read']],
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                summary: 'Liste mes abonnements actifs',
                tags: ['Subscription']
            )
        ),

        new Post(
            openapi: new Operation(
                summary: 'Créer un abonnement',
                description: 'Crée un nouvel abonnement (Subscription) pour un utilisateur.',
                tags: ['Subscription']
            )
        ),
        new Patch(
            openapi: new Operation(
                summary: 'Modifier un abonnement',
                description: 'Modifie les propriétés d’un abonnement existant (date, statut, etc).',
                tags: ['Subscription']
            )
        ),
        new DeleteOperation(
            openapi: new Operation(
                summary: 'Supprimer un abonnement',
                description: 'Supprime un abonnement (Subscription) spécifique.',
                tags: ['Subscription']
            )
        ),
        
    ]
)]
class Subscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $start_date = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $end_date = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'subscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'subscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SubscriptionType $subscriptionType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?string
    {
        return $this->start_date;
    }

    public function setStartDate(string $start_date): static
    {
        $this->start_date = $start_date;
        return $this;
    }

    public function getEndDate(): ?string
    {
        return $this->end_date;
    }

    public function setEndDate(?string $end_date): static
    {
        $this->end_date = $end_date;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getSubscriptionType(): ?SubscriptionType
    {
        return $this->subscriptionType;
    }

    public function setSubscriptionType(?SubscriptionType $subscriptionType): static
    {
        $this->subscriptionType = $subscriptionType;
        return $this;
    }
}

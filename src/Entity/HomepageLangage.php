<?php

namespace App\Entity;

use App\Repository\HomepageLangageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: HomepageLangageRepository::class)]
#[ApiResource(    
    security: "is_granted('ROLE_ADMIN')",
)]  // Ajoute cette annotation pour exposer l'entité dans l'API
class HomepageLangage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'homepageLangages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Homepage $homepage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getHomepage(): ?Homepage
    {
        return $this->homepage;
    }

    public function setHomepage(?Homepage $homepage): static
    {
        $this->homepage = $homepage;

        return $this;
    }
}

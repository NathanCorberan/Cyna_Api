<?php

namespace App\Entity;

use App\Repository\HomepageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: HomepageRepository::class)]
#[ApiResource(    
    security: "is_granted('ROLE_ADMIN')",
)]  // Ajoute cette annotation pour exposer l'entité dans l'API
class Homepage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $image_link = null;

    /**
     * @var Collection<int, HomepageLangage>
     */
    #[ORM\OneToMany(targetEntity: HomepageLangage::class, mappedBy: 'homepage')]
    private Collection $homepageLangages;

    public function __construct()
    {
        $this->homepageLangages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImageLink(): ?string
    {
        return $this->image_link;
    }

    public function setImageLink(?string $image_link): static
    {
        $this->image_link = $image_link;

        return $this;
    }

    /**
     * @return Collection<int, HomepageLangage>
     */
    public function getHomepageLangages(): Collection
    {
        return $this->homepageLangages;
    }

    public function addHomepageLangage(HomepageLangage $homepageLangage): static
    {
        if (!$this->homepageLangages->contains($homepageLangage)) {
            $this->homepageLangages->add($homepageLangage);
            $homepageLangage->setHomepage($this);
        }

        return $this;
    }

    public function removeHomepageLangage(HomepageLangage $homepageLangage): static
    {
        if ($this->homepageLangages->removeElement($homepageLangage)) {
            // set the owning side to null (unless already changed)
            if ($homepageLangage->getHomepage() === $this) {
                $homepageLangage->setHomepage(null);
            }
        }

        return $this;
    }
}

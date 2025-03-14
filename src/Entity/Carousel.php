<?php

namespace App\Entity;

use App\Repository\CarouselRepository;
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

#[ORM\Entity(repositoryClass: CarouselRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_PANEL_ORDER', fields: ['panel_order'])]
#[ApiResource(
    normalizationContext: ['groups' => ['carousel:read']],
    denormalizationContext: ['groups' => ['carousel:write']],
    order: ['panel_order' => 'ASC'],
    operations: [
        new GetCollection(),
        new Get(security: "is_granted('ROLE_ADMIN')"), 
        new Post(security: "is_granted('ROLE_ADMIN')"), 
        new Patch(security: "is_granted('ROLE_ADMIN')"), 
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ]
    
)] 
class Carousel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['carousel:read', 'carousel:write'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['carousel:read', 'carousel:write'])]
    private ?string $image_link = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['carousel:read', 'carousel:write'])]
    private ?int $panel_order = null;

    /**
     * @var Collection<int, CarouselLangage>
     */
    #[ORM\OneToMany(targetEntity: CarouselLangage::class, mappedBy: 'carousel')]
    #[Groups(['carousel:read'])]
    private Collection $carouselLangages;

    public function __construct()
    {
        $this->carouselLangages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImageLink(): ?string
    {
        return $this->image_link;
    }

    public function setImageLink(string $image_link): static
    {
        $this->image_link = $image_link;
        return $this;
    }

    public function getPanelOrder(): ?int
    {
        return $this->panel_order;
    }

    public function setPanelOrder(int $panel_order): static
    {
        $this->panel_order = $panel_order;
        return $this;
    }

    /**
     * @return Collection<int, CarouselLangage>
     */
    public function getCarouselLangages(): Collection
    {
        return $this->carouselLangages;
    }

    public function addCarouselLangage(CarouselLangage $carouselLangage): static
    {
        if (!$this->carouselLangages->contains($carouselLangage)) {
            $this->carouselLangages->add($carouselLangage);
            $carouselLangage->setCarousel($this);
        }

        return $this;
    }

    public function removeCarouselLangage(CarouselLangage $carouselLangage): static
    {
        if ($this->carouselLangages->removeElement($carouselLangage)) {
            if ($carouselLangage->getCarousel() === $this) {
                $carouselLangage->setCarousel(null);
            }
        }

        return $this;
    }
}

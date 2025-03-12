<?php

namespace App\Entity;

use App\Repository\CarouselRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarouselRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_PANEL_ORDER', fields: ['panel_order'])]
class Carousel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $image_link = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $panel_order = null;

    /**
     * @var Collection<int, CarouselLangage>
     */
    #[ORM\OneToMany(targetEntity: CarouselLangage::class, mappedBy: 'carousel')]
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
            // set the owning side to null (unless already changed)
            if ($carouselLangage->getCarousel() === $this) {
                $carouselLangage->setCarousel(null);
            }
        }

        return $this;
    }
}

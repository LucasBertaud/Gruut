<?php

namespace App\Entity;

use App\Repository\ComponentProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ComponentProductRepository::class)]
class ComponentProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'componentProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\ManyToMany(targetEntity: Component::class, inversedBy: 'componentProducts')]
    private Collection $component;

    #[ORM\Column]
    private ?int $quantity = null;

    public function __construct()
    {
        $this->component = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return Collection<int, Component>
     */
    public function getComponent(): Collection
    {
        return $this->component;
    }

    public function addComponent(Component $component): self
    {
        if (!$this->component->contains($component)) {
            $this->component->add($component);
        }

        return $this;
    }

    public function removeComponent(Component $component): self
    {
        $this->component->removeElement($component);

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }
}

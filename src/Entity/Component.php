<?php

namespace App\Entity;

use App\Repository\ComponentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\This;

#[ORM\Entity(repositoryClass: ComponentRepository::class)]
class Component
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $provider = null;

    #[ORM\Column]
    private ?int $stock = null;

    #[ORM\ManyToMany(targetEntity: ComponentProduct::class, mappedBy: 'component')]
    private Collection $componentProducts;

    public function __toString()
    {
        return $this->getName();
    }
    
    public function __construct()
    {
        $this->componentProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * @return Collection<int, ComponentProduct>
     */
    public function getComponentProducts(): Collection
    {
        return $this->componentProducts;
    }

    public function addComponentProduct(ComponentProduct $componentProduct): self
    {
        if (!$this->componentProducts->contains($componentProduct)) {
            $this->componentProducts->add($componentProduct);
            $componentProduct->addComponent($this);
        }

        return $this;
    }

    public function removeComponentProduct(ComponentProduct $componentProduct): self
    {
        if ($this->componentProducts->removeElement($componentProduct)) {
            $componentProduct->removeComponent($this);
        }

        return $this;
    }
}

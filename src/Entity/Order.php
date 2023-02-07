<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\This;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: true, onDelete : "SET NULL")]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(length: 255)]
    private ?string $carrier_name = null;

    #[ORM\Column]
    private ?float $carrier_price = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $delivery = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

   
    #[ORM\OneToMany(mappedBy: 'my_order', targetEntity: OrderDetails::class)]
    private Collection $orderDetails;

    #[ORM\Column(length: 255)]
    private ?string $stripeSessionId = null;

    #[ORM\Column]
    private ?bool $isPaid = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bill = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?\DateTimeImmutable $billing_date = null;

    public function __construct()
    {
        $this->orderDetails = new ArrayCollection();
    }

    public function getTotalProduct()
    {
        $total = 0;
        foreach($this->getOrderDetails()->getValues() as $product)
        {
            $total += $product->getPrice() * $product->getQuantity();
            
        };
                
        return $total;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getCarrierName(): ?string
    {
        return $this->carrier_name;
    }

    public function setCarrierName(string $carrier_name): self
    {
        $this->carrier_name = $carrier_name;

        return $this;
    }

    public function getCarrierPrice(): ?float
    {
        return $this->carrier_price;
    }

    public function setCarrierPrice(float $carrier_price): self
    {
        $this->carrier_price = $carrier_price;

        return $this;
    }

    public function getDelivery(): ?string
    {
        return $this->delivery;
    }

    public function setDelivery(string $delivery): self
    {
        $this->delivery = $delivery;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }
   

    /**
     * @return Collection<int, OrderDetails>
     */
    public function getOrderDetails(): Collection
    {
        return $this->orderDetails;
    }

    public function addOrderDetail(OrderDetails $orderDetail): self
    {
        if (!$this->orderDetails->contains($orderDetail)) {
            $this->orderDetails->add($orderDetail);
            $orderDetail->setMyOrder($this);
        }

        return $this;
    }

    public function removeOrderDetail(OrderDetails $orderDetail): self
    {
        if ($this->orderDetails->removeElement($orderDetail)) {
            // set the owning side to null (unless already changed)
            if ($orderDetail->getMyOrder() === $this) {
                $orderDetail->setMyOrder(null);
            }
        }

        return $this;
    }

    public function getStripeSessionId(): ?string
    {
        return $this->stripeSessionId;
    }

    public function setStripeSessionId(string $stripeSessionId): self
    {
        $this->stripeSessionId = $stripeSessionId;

        return $this;
    }

    public function getIsPaid(): ?bool
    {
        return $this->isPaid;
    }

    public function setIsPaid(bool $isPaid): self
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    public function getBill(): ?string
    {
        return $this->bill;
    }

    public function setBill(?string $bill): self
    {
        $this->bill = $bill;

        return $this;
    }

    public function getBillingDate(): ?\DateTimeImmutable
    {
        return $this->billing_date;
    }

    public function setBillingDate(\DateTimeImmutable $billing_date): self
    {
        $this->billing_date = $billing_date;

        return $this;
    }
}

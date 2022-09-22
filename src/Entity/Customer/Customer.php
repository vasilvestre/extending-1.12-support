<?php

declare(strict_types=1);

namespace App\Entity\Customer;

use App\Entity\Product\Product;
use App\Validator\Constraint\VatNumber;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Customer as BaseCustomer;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: "sylius_customer")]
class Customer extends BaseCustomer
{
    #[VatNumber]
    #[Groups(['shop:customer:create', 'shop:customer:update', 'shop:customer:read'])]
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $vatNumber = null;

    #[Groups('shop:customer:read')]
    #[ORM\OneToMany(mappedBy: 'publishedBy', targetEntity: Product::class)]
    private Collection $products;

    public function getVatNumber(): ?string
    {
        return $this->vatNumber;
    }

    public function setVatNumber(?string $vatNumber): self
    {
        $this->vatNumber = $vatNumber;

        return $this;
    }

    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function setProducts(Collection $products): Customer
    {
        $this->products = $products;
        return $this;
    }
}

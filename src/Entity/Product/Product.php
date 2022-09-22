<?php

declare(strict_types=1);

namespace App\Entity\Product;

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Entity\Customer\Customer;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\Component\Product\Model\ProductTranslationInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product')]
class Product extends BaseProduct
{
    #[ApiProperty(readableLink: true)]
    #[Groups('shop:product:read')]
    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Customer $publishedBy;

    public function isPublishedBy(): ?Customer
    {
        return $this->publishedBy;
    }

    public function setPublishedBy(?Customer $publishedBy): void
    {
        $this->publishedBy = $publishedBy;
    }
    protected function createTranslation(): ProductTranslationInterface
    {
        return new ProductTranslation();
    }
}

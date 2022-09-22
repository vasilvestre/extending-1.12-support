<?php

namespace App\Command\Product;

use App\Entity\Taxonomy\Taxon;
use Symfony\Component\Serializer\Annotation\Groups;

class NewProduct
{
    public function __construct(
        #[Groups('shop:product:create')] public Taxon $taxon,
        #[Groups('shop:product:create')] public string $price,
        #[Groups('shop:product:create')] public string $name,
        #[Groups('shop:product:create')] public string $description,
    ){
    }
}

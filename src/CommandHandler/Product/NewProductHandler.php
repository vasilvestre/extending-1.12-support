<?php

declare(strict_types=1);

namespace App\CommandHandler\Product;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use App\Command\Product\NewProduct;
use App\Entity\Channel\ChannelPricing;
use App\Entity\Customer\Customer;
use App\Entity\Product\Product;
use App\Entity\Product\ProductTaxon;
use App\Entity\Product\ProductVariant;
use App\Entity\Taxation\TaxCategory;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Taxation\Repository\TaxCategoryRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NewProductHandler implements MessageHandlerInterface
{
    public function __construct(
        private CustomerContextInterface $context,
        private ProductFactoryInterface $productFactory,
        private ChannelContextInterface $channelContext,
        private FactoryInterface $channelPricingFactory,
        private FactoryInterface $productTaxonFactory,
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger,
        private TaxCategoryRepositoryInterface $taxCategoryRepository,
        private ValidatorInterface $validator,
    ) {
    }

    public function __invoke(NewProduct $command): void
    {
        /** @var Customer $customer */
        $customer = $this->context->getCustomer();
        /** @var Product $product */
        $product = $this->productFactory->createWithVariant();
        /** @var ProductTaxon $productTaxon */
        $productTaxon = $this->productTaxonFactory->createNew();
        $productTaxon->setTaxon($command->taxon);
        $product->setName($command->name);
        $product->setCode($this->slugger->slug($command->name)->toString());
        $product->setSlug($this->slugger->slug($command->name)->toString());
        $product->addChannel($this->channelContext->getChannel());
        $product->setMainTaxon($command->taxon);
        $product->addProductTaxon($productTaxon);
        $product->setDescription($command->description);
        $product->setPublishedBy($customer);

        /** @var ChannelPricing $channelPricing */
        $channelPricing = $this->channelPricingFactory->createNew();
        $channelPricing->setPrice((int) $command->price);
        $channelPricing->setChannelCode($this->channelContext->getChannel()->getCode());

        /** @var ProductVariant $variant */
        $variant = $product->getVariants()->current();
        $variant->setName($command->name);
        $variant->setCode($this->slugger->slug($command->name)->toString());
        $variant->setEnabled(true);
        $variant->addChannelPricing($channelPricing);
        if (/** @var TaxCategory $tax */ $tax = $this->taxCategoryRepository->findOneBy(['code' => 'all'])) {
            $variant->setTaxCategory($tax);
        }

        $violations = $this->validator->validate(value: $product, groups: ['default', 'sylius']);
        if (0 !== \count($violations)) {
            throw new ValidationException($violations);
        }
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }
}

<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use Sylius\Bundle\ApiBundle\Context\UserContextInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;

final class CustomerItemDataProvider implements RestrictedDataProviderInterface, ItemDataProviderInterface
{
    public function __construct(
        private UserContextInterface $userContext,
        private CustomerRepositoryInterface $customerRepository,
    ) {
    }

    /**
     * @param string[] $context
     * @param string   $id
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        $user = $this->userContext->getUser();

        if ($user instanceof AdminUserInterface && in_array('ROLE_API_ACCESS', $user->getRoles(), true)) {
            return $this->customerRepository->find($id);
        }

        if ($user instanceof ShopUserInterface) {
            return $this->customerRepository->find($id);
        }

        if ($user === null && $operationName === 'shop_verify_customer_account') {
            return $this->customerRepository->find($id);
        }

        return null;
    }

    /**
     * @param string[] $context
     */
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return is_a($resourceClass, CustomerInterface::class, true);
    }
}

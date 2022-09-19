<?php

namespace App\Command\Account;

use Sylius\Bundle\ApiBundle\Command\Account\RegisterShopUser as BaseRegisterShopUser;
use Symfony\Component\Serializer\Annotation\Groups;

class RegisterShopUser extends BaseRegisterShopUser
{
    public function __construct(
        string $firstName,
        string $lastName,
        string $email,
        string $password,
        bool $subscribedToNewsletter = false,
        #[Groups('shop:customer:create')] public ?string $vatNumber = null
    ) {
        parent::__construct($firstName, $lastName, $email, $password, $subscribedToNewsletter);
    }
}

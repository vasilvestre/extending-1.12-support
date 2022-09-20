<?php

declare(strict_types=1);

namespace App\CommandHandler\Account;

use App\Command\Account\RegisterShopUser;
use App\Entity\Customer\Customer;
use App\Exception\InvalidVatException;
use App\Mangopay\Helper\UserHelper;
use Doctrine\Persistence\ObjectManager;
use Ibericode\Vat\Validator;
use Sylius\Bundle\ApiBundle\Command\Account\SendAccountRegistrationEmail;
use Sylius\Bundle\ApiBundle\Command\Account\SendAccountVerificationEmail;
use Sylius\Bundle\CoreBundle\Resolver\CustomerResolverInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\User\Security\Generator\GeneratorInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

class RegisterShopUserHandler implements MessageHandlerInterface
{
    public function __construct(
        private FactoryInterface $shopUserFactory,
        private ObjectManager $shopUserManager,
        private CustomerResolverInterface $customerProvider,
        private ChannelRepositoryInterface $channelRepository,
        private GeneratorInterface $tokenGenerator,
        private MessageBusInterface $commandBus,
        private UserHelper $userLayout,
        private Validator $vatValidator,
    ) {
    }

    public function __invoke(RegisterShopUser $command): ShopUserInterface
    {
        /** @var ShopUserInterface $user */
        $user = $this->shopUserFactory->createNew();
        $user->setPlainPassword($command->password);

        /** @var Customer $customer */
        $customer = $this->customerProvider->provide($command->email);

        if ($customer->getUser() !== null) {
            throw new \DomainException(sprintf('User with email "%s" is already registered.', $command->email));
        }

        $customer->setFirstName($command->firstName);
        $customer->setLastName($command->lastName);
        if ($command->vatNumber !== null) {
            if ($this->vatValidator->validateVatNumberFormat($command->vatNumber) === false) {
                throw new InvalidVatException(sprintf('VAT number format "%s" is invalid.', $command->vatNumber));
            }
            if ($this->vatValidator->validateVatNumber($command->vatNumber) === false) {
                throw new InvalidVatException(sprintf('VAT number "%s" is invalid.', $command->vatNumber));
            }
            $customer->setVatNumber($command->vatNumber);
        } else {
            throw new InvalidVatException('VAT number is empty.');
        }
        $customer->setUser($user);

        /** @var ChannelInterface $channel */
        $channel = $this->channelRepository->findOneByCode($command->channelCode);

        $this->shopUserManager->persist($user);
        
        $this->commandBus->dispatch(new SendAccountRegistrationEmail(
            $command->email,
            $command->localeCode,
            $command->channelCode
        ), [new DispatchAfterCurrentBusStamp()]);

        if (!$channel->isAccountVerificationRequired()) {
            $user->setEnabled(true);

            return $user;
        }

        $token = $this->tokenGenerator->generate();
        $user->setEmailVerificationToken($token);

        $this->commandBus->dispatch(new SendAccountVerificationEmail(
            $command->email,
            $command->localeCode,
            $command->channelCode
        ), [new DispatchAfterCurrentBusStamp()]);

        return $user;
    }
}

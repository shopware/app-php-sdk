<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Response\Customer;

use Shopware\App\SDK\Context\Response\ResponseStruct;

class CustomerResponseStruct extends ResponseStruct
{
    public ?string $title;

    /**
     * @var 'private'|'business'|null
     */
    public ?string $accountType = null;

    public string $firstName;

    public string $lastName;

    public string $email;

    public ?string $salutationId = null;

    public bool $guest = true;

    /**
     * You find available domains in the sales channel context -> sales channel -> domains
     */
    public string $storefrontUrl;

    public ?string $requestedGroupId = null;

    public ?string $affiliateCode = null;

    public ?string $campaignCode = null;

    public ?int $birthdayDay = null;

    public ?int $birthdayMonth = null;

    public ?int $birthdayYear = null;

    /**
     * You'll need to set a password if you want to create a non-guest customer
     * Be aware to supply a plain password, it will be hashed before it is stored by the shop instance
     */
    public ?string $password = null;

    public AddressResponseStruct $billingAddress;

    public ?AddressResponseStruct $shippingAddress = null;

    /**
     * @var string[]
     */
    public array $vatIds = [];

    public bool $acceptedDataProtection = false;

    /**
     * @var array<string, mixed>
     */
    public array $customFields = [];
}

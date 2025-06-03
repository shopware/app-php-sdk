<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Response\Customer;

use Shopware\App\SDK\Context\Response\ResponseStruct;

class AddressResponseStruct extends ResponseStruct
{
    public ?string $title = null;
    public string $firstName;
    public string $lastName;
    public ?string $salutationId = null;
    public string $street;
    public string $zipcode;
    public string $city;
    public ?string $company = null;
    public ?string $department = null;
    public ?string $countryStateId = null;
    public string $countryId;
    public ?string $additionalAddressLine1 = null;
    public ?string $additionalAddressLine2 = null;
    public ?string $phoneNumber = null;
}

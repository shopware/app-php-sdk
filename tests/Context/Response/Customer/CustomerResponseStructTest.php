<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Response\Customer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Response\Customer\AddressResponseStruct;
use Shopware\App\SDK\Context\Response\Customer\CustomerResponseStruct;

#[CoversClass(CustomerResponseStruct::class)]
class CustomerResponseStructTest extends TestCase
{
    public function testCustomFieldsDefaultAndAssignment(): void
    {
        $customer = new CustomerResponseStruct();

        static::assertIsArray($customer->customFields);
        static::assertEmpty($customer->customFields);

        $customFields = [
            'string_field' => 'value',
            'int_field' => 42,
            'bool_field' => true,
            'nested_field' => ['nested' => 'array'],
        ];
        $customer->customFields = $customFields;

        static::assertSame($customFields, $customer->customFields);
    }

    public function testCustomFieldsSerialization(): void
    {
        $billingAddress = new AddressResponseStruct();
        $billingAddress->firstName = 'John';
        $billingAddress->lastName = 'Doe';
        $billingAddress->street = 'Main Street 1';
        $billingAddress->zipcode = '12345';
        $billingAddress->city = 'Springfield';
        $billingAddress->countryId = 'country-id-123';

        $customer = new CustomerResponseStruct();
        $customer->firstName = 'John';
        $customer->lastName = 'Doe';
        $customer->email = 'john.doe@example.com';
        $customer->storefrontUrl = 'https://example.com';
        $customer->billingAddress = $billingAddress;
        $customer->customFields = [
            'vip' => true,
            'points' => 500,
            'metadata' => ['source' => 'api'],
        ];

        $serialized = $customer->jsonSerialize();

        static::assertArrayHasKey('customFields', $serialized);
        static::assertSame([
            'vip' => true,
            'points' => 500,
            'metadata' => ['source' => 'api'],
        ], $serialized['customFields']);
    }

    public function testEmptyCustomFieldsSerialization(): void
    {
        $billingAddress = new AddressResponseStruct();
        $billingAddress->firstName = 'Jane';
        $billingAddress->lastName = 'Smith';
        $billingAddress->street = 'Oak Avenue 10';
        $billingAddress->zipcode = '54321';
        $billingAddress->city = 'Metropolis';
        $billingAddress->countryId = 'country-id-456';

        $customer = new CustomerResponseStruct();
        $customer->firstName = 'Jane';
        $customer->lastName = 'Smith';
        $customer->email = 'jane.smith@example.com';
        $customer->storefrontUrl = 'https://example.com';
        $customer->billingAddress = $billingAddress;

        $serialized = $customer->jsonSerialize();

        static::assertArrayHasKey('customFields', $serialized);
        static::assertEmpty($serialized['customFields']);
    }
}

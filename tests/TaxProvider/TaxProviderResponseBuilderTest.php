<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\TaxProvider;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\TaxProvider\CalculatedTax;
use Shopware\App\SDK\TaxProvider\TaxProviderResponseBuilder;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaxProviderResponseBuilder::class)]
class TaxProviderResponseBuilderTest extends TestCase
{
    public function testGlobalTax(): void
    {
        $builder = new TaxProviderResponseBuilder();
        $builder->addCartTax(new CalculatedTax(19, 100, 19));

        $response = $builder->build();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame(
            '{"lineItemTaxes":[],"deliveryTaxes":[],"cartPriceTaxes":{"100":{"tax":19,"taxRate":100,"price":19}}}',
            $response->getBody()->getContents()
        );
    }

    public function testGlobalTaxIsAddedUp(): void
    {
        $builder = new TaxProviderResponseBuilder();
        $builder->addCartTax(new CalculatedTax(19, 100, 19));
        $builder->addCartTax(new CalculatedTax(19, 100, 19));


        $response = $builder->build();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame(
            '{"lineItemTaxes":[],"deliveryTaxes":[],"cartPriceTaxes":{"100":{"tax":38,"taxRate":100,"price":38}}}',
            $response->getBody()->getContents()
        );
    }

    public function testLineItemTax(): void
    {
        $builder = new TaxProviderResponseBuilder();
        $builder->addLineItemTax('lineItem1', new CalculatedTax(19, 100, 19));

        $response = $builder->build();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame(
            '{"lineItemTaxes":{"lineItem1":{"100":{"tax":19,"taxRate":100,"price":19}}},"deliveryTaxes":[],"cartPriceTaxes":[]}',
            $response->getBody()->getContents()
        );
    }

    public function testLineItemTaxIsOverridden(): void
    {
        $builder = new TaxProviderResponseBuilder();
        $builder->addLineItemTax('lineItem1', new CalculatedTax(19, 100, 19));
        $builder->addLineItemTax('lineItem1', new CalculatedTax(38, 100, 19));

        $response = $builder->build();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame(
            '{"lineItemTaxes":{"lineItem1":{"100":{"tax":38,"taxRate":100,"price":19}}},"deliveryTaxes":[],"cartPriceTaxes":[]}',
            $response->getBody()->getContents()
        );
    }

    public function testDeliveryTax(): void
    {
        $builder = new TaxProviderResponseBuilder();
        $builder->addDeliveryTax('delivery1', new CalculatedTax(19, 100, 19));

        $response = $builder->build();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame(
            '{"lineItemTaxes":[],"deliveryTaxes":{"delivery1":{"100":{"tax":19,"taxRate":100,"price":19}}},"cartPriceTaxes":[]}',
            $response->getBody()->getContents()
        );
    }

    public function testBuildPayload(): void
    {
        $builder = new TaxProviderResponseBuilder();
        $builder->addCartTax(new CalculatedTax(19, 100, 19));

        $response = $builder->buildPayload();

        static::assertSame(
            '{"lineItemTaxes":[],"deliveryTaxes":[],"cartPriceTaxes":{"100":{"tax":19,"taxRate":100,"price":19}}}',
            $response
        );
    }
}

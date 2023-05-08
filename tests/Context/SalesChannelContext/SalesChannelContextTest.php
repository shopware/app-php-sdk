<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Context\ArrayStruct;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\RoundingConfig;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelContext;

#[CoversClass(ArrayStruct::class)]
#[CoversClass(RoundingConfig::class)]
#[CoversClass(SalesChannelContext::class)]
class SalesChannelContextTest extends TestCase
{
    public function testCustomer(): void
    {
        $context = new SalesChannelContext(['customer' => null]);
        static::assertNull($context->getCustomer());
    }

    public function testGetCurrencyId(): void
    {
        $context = new SalesChannelContext(['context' => ['currencyId' => 'foo']]);
        static::assertSame('foo', $context->getCurrencyId());
    }

    public function testGetTaxState(): void
    {
        $context = new SalesChannelContext(['context' => ['taxState' => 'foo']]);
        static::assertSame('foo', $context->getTaxState());
    }

    public function testGetRounding(): void
    {
        $context = new SalesChannelContext(['context' => ['rounding' => ['decimals' => 2]]]);
        static::assertSame(2, $context->getRounding()->getDecimals());
    }
}

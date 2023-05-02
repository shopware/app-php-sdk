<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Context\ArrayStruct;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelContext;

#[CoversClass(SalesChannelContext::class)]
#[CoversClass(ArrayStruct::class)]
class SalesChannelContextTest extends TestCase
{
    public function testCustomer(): void
    {
        $context = new SalesChannelContext(['customer' => null]);
        static::assertNull($context->getCustomer());
    }
}

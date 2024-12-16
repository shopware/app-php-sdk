<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Gateway\InAppFeatures;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Gateway\InAppFeatures\FilterAction;
use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(FilterAction::class)]
class FilterActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $purchases = new Collection(['purchase-1', 'purchase-2', 'purchase-3']);

        $shop = new MockShop('foo', 'https://example.com', 'secret');
        $source = new ActionSource('https://example.com', '1.0.0', new Collection());

        $action = new FilterAction($shop, $source, $purchases);

        static::assertSame($shop, $action->shop);
        static::assertSame($source, $action->source);
        static::assertSame($purchases, $action->purchases);
    }
}

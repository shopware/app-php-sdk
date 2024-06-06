<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Module;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Module\ModuleAction;
use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(ModuleAction::class)]
class ModuleActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $action = new ModuleAction(
            new MockShop('shop-id', 'https://example.com', 'secret'),
            '1.0.0',
            'de-DE',
            'en-GB',
            new Collection()
        );

        static::assertSame('shop-id', $action->shop->getShopId());
        static::assertSame('https://example.com', $action->shop->getShopUrl());
        static::assertSame('secret', $action->shop->getShopSecret());
        static::assertSame('1.0.0', $action->shopwareVersion);
        static::assertSame('de-DE', $action->contentLanguage);
        static::assertSame('en-GB', $action->userLanguage);
    }
}

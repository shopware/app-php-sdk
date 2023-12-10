<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Module;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Module\ModuleAction;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(ModuleAction::class)]
class ModuleActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $shop = new MockShop('shop-id', 'shop-url', 'shop-secret');

        $action = new ModuleAction(
            shop: $shop,
            shopwareVersion: '6.4.0',
            contentLanguage: 'content-language',
            userLanguage: 'user-language',
        );

        static::assertSame($shop, $action->shop);
        static::assertSame('6.4.0', $action->shopwareVersion);
        static::assertSame('content-language', $action->contentLanguage);
        static::assertSame('user-language', $action->userLanguage);
    }
}

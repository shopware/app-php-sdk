<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\ActionButton;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionButton\ActionButtonAction;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(ActionButtonAction::class)]
class ActionButtonActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $shop = new MockShop('shop-id', 'shop-url', 'shop-secret');
        $source = new ActionSource('url', 'app-version');

        $action = new ActionButtonAction(
            $shop,
            $source,
            ['id1', 'id2'],
            'entity',
            'action'
        );

        static::assertSame($shop, $action->shop);
        static::assertSame($source, $action->source);
        static::assertSame(['id1', 'id2'], $action->ids);
        static::assertSame('entity', $action->entity);
        static::assertSame('action', $action->action);
    }
}

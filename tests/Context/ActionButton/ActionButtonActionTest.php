<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\ActionButton;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\ActionButton\ActionButtonAction;
use Shopware\App\SDK\Shop\ShopInterface;

#[CoversClass(ActionButtonAction::class)]
class ActionButtonActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $mockShop = $this->createMock(ShopInterface::class);
        $mockActionSource = $this->createMock(ActionSource::class);
        $ids = ['1', '2', '3'];
        $entity = 'testEntity';
        $action = 'testAction';

        $actionButtonAction = new ActionButtonAction(
            $mockShop,
            $mockActionSource,
            $ids,
            $entity,
            $action
        );

        static::assertSame($mockShop, $actionButtonAction->shop);
        static::assertSame($mockActionSource, $actionButtonAction->source);
        static::assertSame($ids, $actionButtonAction->ids);
        static::assertSame($entity, $actionButtonAction->entity);
        static::assertSame($action, $actionButtonAction->action);
    }
}

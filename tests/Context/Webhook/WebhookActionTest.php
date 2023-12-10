<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Webhook;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Webhook\WebhookAction;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(WebhookAction::class)]
class WebhookActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $shop = new MockShop('shop-id', 'shop-url', 'shop-secret');
        $source = new ActionSource('source-type', 'source-uri');

        $action = new WebhookAction(
            $shop,
            $source,
            'event-name',
            ['payload'],
            new \DateTime('2021-01-01T00:00:00+00:00')
        );

        static::assertSame($shop, $action->shop);
        static::assertSame($source, $action->source);
        static::assertSame('event-name', $action->eventName);
        static::assertSame(['payload'], $action->payload);
        static::assertSame('2021-01-01T00:00:00+00:00', $action->timestamp->format(\DateTimeInterface::ATOM));
    }
}

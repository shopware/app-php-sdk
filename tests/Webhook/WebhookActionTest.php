<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Webhook;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\InAppPurchase\InAppPurchase;
use Shopware\App\SDK\Context\Webhook\WebhookAction;
use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(WebhookAction::class)]
class WebhookActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $shop = new MockShop('shop-id', 'https://example.com', 'shop-secret');
        $IAPs = new Collection([new InAppPurchase('swagInAppPurchase1', 1), new InAppPurchase('swagInAppPurchase2', 2)]);
        $source = new ActionSource('https://example.com', '1.0.0', $IAPs);
        $eventName = 'order.placed';
        $payload = ['orderId' => 'order-id'];
        $timestamp = new \DateTimeImmutable();

        $webhookAction = new WebhookAction($shop, $source, $eventName, $payload, $timestamp);

        static::assertSame($shop, $webhookAction->shop);
        static::assertSame($source, $webhookAction->source);
        static::assertSame($eventName, $webhookAction->eventName);
        static::assertSame($payload, $webhookAction->payload);
        static::assertSame($timestamp, $webhookAction->timestamp);
        static::assertSame($IAPs, $webhookAction->source->inAppPurchases);
    }
}

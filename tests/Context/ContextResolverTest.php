<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\Context\ActionButton\ActionButton;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\ContextResolver;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Webhook\Webhook;
use Shopware\App\SDK\Exception\MalformedWebhookBodyException;
use Shopware\App\SDK\Shop\ShopInterface;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(ContextResolver::class)]
#[CoversClass(ActionSource::class)]
#[CoversClass(Webhook::class)]
#[CoversClass(ActionButton::class)]
#[CoversClass(MockShop::class)]
#[CoversClass(MalformedWebhookBodyException::class)]
class ContextResolverTest extends TestCase
{
    public function testAssembleWebhookMalformed(): void
    {
        $contextResolver = new ContextResolver();

        static::expectException(MalformedWebhookBodyException::class);
        $contextResolver->assembleWebhook(
            $this->createApiRequest([]),
            $this->getShop()
        );
    }

    public function testAssembleWebhook(): void
    {
        $contextResolver = new ContextResolver();

        $webhook = $contextResolver->assembleWebhook(
            $this->createApiRequest([
                'source' => [
                    'url' => 'https://example.com',
                    'appVersion' => '1.0.0',
                ],
                'data' => [
                    'event' => 'order.placed',
                    'payload' => [
                        'orderId' => '123',
                    ],
                ],
                'timestamp' => 123456789,
            ]),
            $this->getShop()
        );

        static::assertSame('123', $webhook->payload['orderId']);
        static::assertSame('order.placed', $webhook->eventName);
        static::assertSame('https://example.com', $webhook->source->url);
        static::assertSame('1.0.0', $webhook->source->appVersion);
    }

    public function testAssembleActionButtonMalformed(): void
    {
        $contextResolver = new ContextResolver();

        static::expectException(MalformedWebhookBodyException::class);
        $contextResolver->assembleActionButton(
            $this->createApiRequest([]),
            $this->getShop()
        );
    }

    public function testAssembleActionButton(): void
    {
        $contextResolver = new ContextResolver();

        $actionButton = $contextResolver->assembleActionButton(
            $this->createApiRequest([
                'source' => [
                    'url' => 'https://example.com',
                    'appVersion' => '1.0.0',
                ],
                'data' => [
                    'ids' => ['123'],
                    'entity' => 'order',
                    'action' => 'open',
                ],
            ]),
            $this->getShop()
        );

        static::assertSame(['123'], $actionButton->ids);
        static::assertSame('order', $actionButton->entity);
        static::assertSame('open', $actionButton->action);

        static::assertSame('https://example.com', $actionButton->source->url);
        static::assertSame('1.0.0', $actionButton->source->appVersion);
    }

    public function testMalformedSource(): void
    {
        $contextResolver = new ContextResolver();

        static::expectException(MalformedWebhookBodyException::class);
        $contextResolver->assembleActionButton(
            $this->createApiRequest([
                'source' => [
                    'test' => 'https://example.com',
                ],
            ]),
            $this->getShop()
        );
    }

    private function getShop(): ShopInterface
    {
        return new MockShop('shop-id', 'shop-url', 'shop-secret', 'shop-api-key');
    }

    /**
     * @param array<mixed> $json
     *
     * @throws \JsonException
     */
    private function createApiRequest(array $json): RequestInterface
    {
        return new Request('POST', 'https://example.com', [], json_encode($json, JSON_THROW_ON_ERROR));
    }
}

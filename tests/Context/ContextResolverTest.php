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
use Shopware\App\SDK\Context\Module\Module;
use Shopware\App\SDK\Context\Webhook\Webhook;
use Shopware\App\SDK\Exception\MalformedWebhookBodyException;
use Shopware\App\SDK\Shop\ShopInterface;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(ContextResolver::class)]
#[CoversClass(ActionSource::class)]
#[CoversClass(Webhook::class)]
#[CoversClass(ActionButton::class)]
#[CoversClass(Module::class)]
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

    public function testAssembleModule(): void
    {
        $contextResolver = new ContextResolver();

        $module = $contextResolver->assembleModule(
            new Request('GET', 'http://localhost:6001/module/test?shop-id=vvRy7Nv3Bo8mAVda&shop-url=http://localhost:8000&timestamp=1683015472&sw-version=6.5.9999999.9999999-dev&sw-context-language=2fbb5fe2e29a4d70aa5854ce7ce3e20b&sw-user-language=en-GB&shopware-shop-signature=650455d43eda4eeb4c9a12ee0eb15b46ce88776abaf9beb1ffac31be136e1d9b'),
            $this->getShop()
        );

        static::assertSame('6.5.9999999.9999999-dev', $module->shopwareVersion);
        static::assertSame('2fbb5fe2e29a4d70aa5854ce7ce3e20b', $module->contentLanguage);
        static::assertSame('en-GB', $module->userLanguage);
    }

    public function testAssembleModuleInvalid(): void
    {
        $contextResolver = new ContextResolver();

        static::expectException(MalformedWebhookBodyException::class);
        $contextResolver->assembleModule(new Request('GET', '/'), $this->getShop());
    }

    private function getShop(): ShopInterface
    {
        return new MockShop('shop-id', 'shop-url', 'shop-secret');
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

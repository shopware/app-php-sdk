<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\HttpClient;

use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Psr\Http\Client\ClientInterface;
use Shopware\App\SDK\HttpClient\AuthenticatedClient;
use Shopware\App\SDK\HttpClient\ClientFactory;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\HttpClient\LoggerClient;
use Shopware\App\SDK\HttpClient\NullCache;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(ClientFactory::class)]
#[CoversClass(MockShop::class)]
#[CoversClass(AuthenticatedClient::class)]
#[CoversClass(NullCache::class)]
#[CoversClass(LoggerClient::class)]
class ClientFactoryTest extends TestCase
{
    /**
     * Should not throw an exception when discover works
     */
    #[DoesNotPerformAssertions]
    public function testFactory(): void
    {
        $factory = new ClientFactory();
        $factory->createClient(new MockShop('shop-id', 'shop-secret', ''));
    }

    /**
     * Should not throw an exception when discover works
     */
    #[DoesNotPerformAssertions]
    public function testSimpleFactory(): void
    {
        $factory = new ClientFactory();
        $factory->createSimpleClient(new MockShop('shop-id', 'shop-secret', ''));
    }

    public function testFactoryOwnClient(): void
    {
        $testClient = $this->createMock(ClientInterface::class);
        $testClient
            ->expects(static::exactly(2))
            ->method('sendRequest')
            ->willReturn(new Response(200, [], '{"access_token": "a", "expires_in": 3600}'));

        $factory = new ClientFactory(new NullCache(), $testClient);
        $client = $factory->createClient(new MockShop('shop-id', 'shop-secret', ''));

        $client->sendRequest(new Request('GET', 'https://example.com'));
    }
}

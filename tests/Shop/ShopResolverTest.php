<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Shop;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\Authentication\RequestVerifier;
use Shopware\App\SDK\Exception\MissingShopParameterException;
use Shopware\App\SDK\Exception\ShopNotFoundException;
use Shopware\App\SDK\Shop\ShopResolver;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Test\MockShop;
use Shopware\App\SDK\Test\MockShopRepository;

#[CoversClass(ShopResolver::class)]
#[CoversClass(MissingShopParameterException::class)]
#[CoversClass(ShopNotFoundException::class)]
#[CoversClass(MockShopRepository::class)]
#[CoversClass(MockShop::class)]
class ShopResolverTest extends TestCase
{
    private MockShopRepository $shopRepository;
    private ShopResolver $shopResolver;

    protected function setUp(): void
    {
        $this->shopRepository = new MockShopRepository();
        $this->shopResolver = new ShopResolver($this->shopRepository, $this->createMock(RequestVerifier::class));
    }

    public function testResolveSourceInvalidJSON(): void
    {
        static::expectException(MissingShopParameterException::class);
        $this->shopResolver->resolveShop($this->createJsonRequest('{"source": "invalid"}'));
    }

    public function testResolveSourceNotExisting(): void
    {
        static::expectException(ShopNotFoundException::class);
        static::expectExceptionMessage('Shop with id "1" not found');
        $this->shopResolver->resolveShop($this->createJsonRequest('{"source": {"shopId": "1"}}'));
    }

    public function testResolveSource(): void
    {
        $this->shopRepository->createShop(new MockShop('1', 'test.de', 'asd'));
        $shop = $this->shopResolver->resolveShop($this->createJsonRequest('{"source": {"shopId": "1"}}'));
        static::assertSame('1', $shop->getShopId());
    }

    public function testResolveQueryStringInvalid(): void
    {
        static::expectException(MissingShopParameterException::class);
        $this->shopResolver->resolveShop($this->createGetRequest('foo=1'));
    }

    public function testResolveQueryStringNotExisting(): void
    {
        static::expectException(ShopNotFoundException::class);
        static::expectExceptionMessage('Shop with id "1" not found');
        $this->shopResolver->resolveShop($this->createGetRequest('shop-id=1'));
    }

    public function testResolveQueryString(): void
    {
        $this->shopRepository->createShop(new MockShop('1', 'test.de', 'asd'));
        $shop = $this->shopResolver->resolveShop($this->createGetRequest('shop-id=1'));
        static::assertSame('1', $shop->getShopId());
    }

    private function createJsonRequest(string $body): RequestInterface
    {
        return new Request('POST', 'https://example.com', ['Content-Type' => 'application/json'], $body);
    }

    private function createGetRequest(string $query): RequestInterface
    {
        return new Request('GET', 'https://example.com?' . $query);
    }
}

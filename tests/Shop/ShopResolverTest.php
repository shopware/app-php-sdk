<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Shop;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Shopware\App\SDK\Authentication\DualSignatureRequestVerifier;
use Shopware\App\SDK\Exception\MissingShopParameterException;
use Shopware\App\SDK\Exception\ShopNotFoundException;
use Shopware\App\SDK\Shop\ShopResolver;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Test\MockShop;
use Shopware\App\SDK\Test\MockShopRepository;

#[CoversClass(ShopResolver::class)]
class ShopResolverTest extends TestCase
{
    private MockShopRepository $shopRepository;
    private ShopResolver $shopResolver;

    protected function setUp(): void
    {
        $this->shopRepository = new MockShopRepository();
        $this->shopResolver = new ShopResolver($this->shopRepository, $this->createMock(DualSignatureRequestVerifier::class));
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

    public function testResolveFromSourceRequestIsAuthenticated(): void
    {
        $this->shopRepository->createShop(new MockShop('1', 'test.de', 'asd'));

        $request = $this->createJsonRequest('{"source": {"shopId": "1"}}');

        $verifier = static::createMock(DualSignatureRequestVerifier::class);
        $verifier
            ->expects(static::once())
            ->method('authenticatePostRequest')
            ->with($request);

        $resolver = new ShopResolver($this->shopRepository, $verifier);
        $resolver->resolveShop($request);
    }

    public function testResolveFromQueryStringRequestIsAuthenticated(): void
    {
        $this->shopRepository->createShop(new MockShop('1', 'test.de', 'asd'));

        $request = $this->createGetRequest('shop-id=1');

        $verifier = static::createMock(DualSignatureRequestVerifier::class);
        $verifier
            ->expects(static::once())
            ->method('authenticateGetRequest')
            ->with($request);

        $resolver = new ShopResolver($this->shopRepository, $verifier);
        $resolver->resolveShop($request);
    }

    public function testRequestRewindIsCalled(): void
    {
        $this->shopRepository->createShop(new MockShop('1', 'test.de', 'asd'));

        $body = static::createMock(StreamInterface::class);
        $body
            ->expects(static::once())
            ->method('rewind');

        $body
            ->expects(static::once())
            ->method('getContents')
            ->willReturn('{"source": {"shopId": "1"}}');

        $request = $this->createJsonRequest('');
        $request = $request->withBody($body);

        $this->shopResolver->resolveShop($request);
    }

    /**
     * @dataProvider missingSourceParametersProvider
     */
    public function testMissingSourceParameters(string $body): void
    {
        $request = $this->createJsonRequest($body);

        $resolver = new ShopResolver($this->shopRepository, static::createMock(DualSignatureRequestVerifier::class));

        static::expectException(MissingShopParameterException::class);
        $resolver->resolveShop($request);
    }

    public function testMissingShopStorefront(): void
    {
        $resolver = new ShopResolver($this->shopRepository, static::createMock(DualSignatureRequestVerifier::class));

        $request = $this->createGetRequest('shop-id=1');
        $request = $request->withHeader('shopware-app-shop-id', 'test');

        $this->expectException(ShopNotFoundException::class);

        $resolver->resolveShop($request);
    }

    public function testResolveWithStorefront(): void
    {
        $this->shopRepository->createShop(new MockShop('1', 'test.de', 'asd'));

        $requestVerifier = static::createMock(DualSignatureRequestVerifier::class);
        $requestVerifier
            ->expects(static::once())
            ->method('authenticateStorefrontRequest');

        $resolver = new ShopResolver($this->shopRepository, $requestVerifier);

        $request = $this->createGetRequest('shop-id=1');
        $request = $request->withHeader('shopware-app-shop-id', '1');

        $resolver->resolveShop($request);
    }

    /**
     * @return iterable<array{0: string}>
     */
    public static function missingSourceParametersProvider(): iterable
    {
        yield['[]'];
        yield ['{}'];
        yield ['{"source": {}}'];
        yield ['{"source": {"shopId": 1}}'];
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

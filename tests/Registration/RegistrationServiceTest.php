<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Registration;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\AppConfiguration;
use Shopware\App\SDK\Authentication\RequestVerifier;
use Shopware\App\SDK\Authentication\ResponseSigner;
use Shopware\App\SDK\Exception\MissingShopParameterException;
use Shopware\App\SDK\Exception\ShopNotFoundException;
use Shopware\App\SDK\Registration\RandomStringShopSecretGenerator;
use Shopware\App\SDK\Registration\RegistrationService;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Test\MockShop;
use Shopware\App\SDK\Test\MockShopRepository;

#[CoversClass(RegistrationService::class)]
#[CoversClass(AppConfiguration::class)]
#[CoversClass(ResponseSigner::class)]
#[CoversClass(MissingShopParameterException::class)]
#[CoversClass(ShopNotFoundException::class)]
#[CoversClass(MockShop::class)]
#[CoversClass(MockShopRepository::class)]
class RegistrationServiceTest extends TestCase
{
    private RegistrationService $registerService;
    private MockShopRepository $shopRepository;

    protected function setUp(): void
    {
        $appConfiguration = new AppConfiguration('My App', 'my-secret', 'https://my-app.com');
        $this->shopRepository = new MockShopRepository();
        $this->registerService = new RegistrationService(
            $appConfiguration,
            $this->shopRepository,
            $this->createMock(RequestVerifier::class),
            new ResponseSigner($appConfiguration),
            new RandomStringShopSecretGenerator()
        );
    }

    public function testRegisterMissingParameters(): void
    {
        $request = new Request('GET', 'http://localhost');

        static::expectException(MissingShopParameterException::class);

        $this->registerService->handleShopRegistrationRequest($request, '');
    }

    public function testRegisterCreate(): void
    {
        $request = new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890');

        $this->registerService->handleShopRegistrationRequest($request, '');

        $shop = $this->shopRepository->getShopFromId('123');
        static::assertNotNull($shop);

        static::assertEquals('123', $shop->getShopId());
        static::assertEquals('https://my-shop.com', $shop->getShopUrl());
        static::assertNotNull($shop->getShopSecret());
    }

    public function testRegisterUpdate(): void
    {
        $request = new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890');

        $this->shopRepository->createShop(new MockShop('123', 'https://foo.com', '1234567890'));

        $this->registerService->handleShopRegistrationRequest($request, '');

        $shop = $this->shopRepository->getShopFromId('123');
        static::assertNotNull($shop);

        static::assertEquals('123', $shop->getShopId());
        static::assertEquals('https://my-shop.com', $shop->getShopUrl());
        static::assertNotNull($shop->getShopSecret());
    }

    public function testConfirmMissingParameter(): void
    {
        $request = new Request('POST', 'http://localhost', [], '{}');

        static::expectException(MissingShopParameterException::class);
        $this->registerService->handleConfirmation($request);
    }

    public function testConfirmNotExistingShop(): void
    {
        $request = new Request('POST', 'http://localhost', [], '{"shopId": "123", "apiKey": "1", "secretKey": "1"}');

        static::expectException(ShopNotFoundException::class);
        $this->registerService->handleConfirmation($request);
    }

    public function testConfirm(): void
    {
        $this->shopRepository->createShop(new MockShop('123', 'https://foo.com', '1234567890'));

        $request = new Request('POST', 'http://localhost', [], '{"shopId": "123", "apiKey": "1", "secretKey": "2"}');

        $this->registerService->handleConfirmation($request);

        $shop = $this->shopRepository->getShopFromId('123');
        static::assertNotNull($shop);

        static::assertEquals('1', $shop->getClientId());
        static::assertEquals('2', $shop->getClientSecret());
    }
}
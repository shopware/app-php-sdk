<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Registration;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shopware\App\SDK\AppConfiguration;
use Shopware\App\SDK\Authentication\RequestVerifier;
use Shopware\App\SDK\Authentication\ResponseSigner;
use Shopware\App\SDK\Event\BeforeRegistrationCompletedEvent;
use Shopware\App\SDK\Event\BeforeRegistrationStartsEvent;
use Shopware\App\SDK\Event\RegistrationCompletedEvent;
use Shopware\App\SDK\Exception\MissingShopParameterException;
use Shopware\App\SDK\Exception\ShopNotFoundException;
use Shopware\App\SDK\Registration\RandomStringShopSecretGenerator;
use Shopware\App\SDK\Registration\RegistrationService;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Shop\ShopRepositoryInterface;
use Shopware\App\SDK\Test\MockShop;
use Shopware\App\SDK\Test\MockShopRepository;

#[CoversClass(RegistrationService::class)]
class RegistrationServiceTest extends TestCase
{
    private RegistrationService $registerService;
    private MockShopRepository $shopRepository;
    private AppConfiguration $appConfiguration;

    protected function setUp(): void
    {
        $this->appConfiguration = new AppConfiguration('My App', 'my-secret', 'http://localhost');
        $this->shopRepository = new MockShopRepository();
        $this->registerService = new RegistrationService(
            $this->appConfiguration,
            $this->shopRepository,
            $this->createMock(RequestVerifier::class),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator()
        );
    }

    public function testRegisterMissingParameters(): void
    {
        $request = new Request('GET', 'http://localhost');

        $this->expectException(MissingShopParameterException::class);

        $this->registerService->register($request);
    }

    public function testRegisterCreate(): void
    {
        $events = [];

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->willReturnCallback(function ($event) use (&$events) {
                $events[] = $event;
            });

        $shopRepository = $this->createMock(ShopRepositoryInterface::class);

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $shopRepository,
            $this->createMock(RequestVerifier::class),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger(),
            $eventDispatcher
        );

        $shopRepository
            ->expects(static::once())
            ->method('getShopFromId')
            ->willReturn(null);

        $shop = new MockShop('123', 'https://my-shop.com', '1234567890');

        $shopRepository
            ->expects(static::once())
            ->method('createShopStruct')
            ->willReturn($shop);

        $shopRepository
            ->expects(static::never())
            ->method('updateShop')
            ->with($shop);

        $eventDispatcher
            ->expects(static::once())
            ->method('dispatch');

        $response = $registrationService->register(
            new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890')
        );

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('https://my-shop.com', $shop->getShopUrl());

        $json = json_decode((string)$response->getBody()->getContents(), true);

        static::assertCount(1, $events);
        static::assertInstanceOf(BeforeRegistrationStartsEvent::class, $events[0]);

        static::assertIsArray($json);
        static::assertArrayHasKey('proof', $json);
        static::assertArrayHasKey('confirmation_url', $json);
        static::assertArrayHasKey('secret', $json);
    }

    public function testRegisterCreateMustNotDispatchBeforeRegistrationStartsEvent(): void
    {
        $shopRepository = $this->createMock(ShopRepositoryInterface::class);

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $shopRepository,
            $this->createMock(RequestVerifier::class),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger(),
            null
        );

        $shopRepository
            ->expects(static::once())
            ->method('getShopFromId')
            ->willReturn(null);

        $shop = new MockShop('123', 'https://my-shop.com', '1234567890');

        $shopRepository
            ->expects(static::once())
            ->method('createShopStruct')
            ->willReturn($shop);

        $shopRepository
            ->expects(static::never())
            ->method('updateShop')
            ->with($shop);

        $response = $registrationService->register(
            new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890')
        );

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('https://my-shop.com', $shop->getShopUrl());

        $json = json_decode((string)$response->getBody()->getContents(), true);

        static::assertIsArray($json);
        static::assertArrayHasKey('proof', $json);
        static::assertArrayHasKey('confirmation_url', $json);
        static::assertArrayHasKey('secret', $json);
    }

    public function testRegisterUpdate(): void
    {
        $events = [];

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->willReturnCallback(function ($event) use (&$events) {
                $events[] = $event;
            });

        $shopRepository = $this->createMock(ShopRepositoryInterface::class);

        $shop = new MockShop('123', 'https://my-shop.com', '1234567890');

        $shopRepository
            ->expects(static::once())
            ->method('getShopFromId')
            ->willReturn($shop);

        $shopRepository
            ->expects(static::never())
            ->method('createShopStruct');

        $shopRepository
            ->expects(static::once())
            ->method('updateShop')
            ->with($this->callback(function (MockShop $shop) {
                return $shop->getShopUrl() === 'https://my-shop.com';
            }));

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $shopRepository,
            $this->createMock(RequestVerifier::class),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger(),
            $eventDispatcher
        );

        $registrationService->register(
            new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890')
        );

        static::assertNotNull($shop);

        static::assertCount(1, $events);
        static::assertInstanceOf(BeforeRegistrationStartsEvent::class, $events[0]);

        static::assertEquals('123', $shop->getShopId());
        static::assertEquals('https://my-shop.com', $shop->getShopUrl());
        static::assertNotNull($shop->getShopSecret());
    }

    public function testRegisterUpdateMustNotDispatchBeforeRegistrationStartsEvent(): void
    {
        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $this->shopRepository,
            $this->createMock(RequestVerifier::class),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger(),
            null
        );

        $request = new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890');

        $registrationService->register($request);

        $shop = $this->shopRepository->getShopFromId('123');

        $this->shopRepository->updateShop($shop);

        static::assertNotNull($shop);

        static::assertEquals('123', $shop->getShopId());
        static::assertEquals('https://my-shop.com', $shop->getShopUrl());
        static::assertNotNull($shop->getShopSecret());
    }

    public function testConfirmMissingParameter(): void
    {
        $request = new Request('POST', 'http://localhost', [], '{}');

        $this->expectException(MissingShopParameterException::class);
        $this->registerService->registerConfirm($request);
    }

    public function testConfirmNotExistingShop(): void
    {
        $request = new Request('POST', 'http://localhost', [], '{"shopId": "123", "apiKey": "1", "secretKey": "1"}');

        $this->expectException(ShopNotFoundException::class);
        $this->registerService->registerConfirm($request);
    }

    public function testConfirm(): void
    {
        $events = [];
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->method('dispatch')
            ->willReturnCallback(function ($event) use (&$events) {
                $events[] = $event;
            });

        $this->registerService = new RegistrationService(
            $this->appConfiguration,
            $this->shopRepository,
            $this->createMock(RequestVerifier::class),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger(),
            $eventDispatcher
        );

        $this->shopRepository->createShop(new MockShop('123', 'https://foo.com', '1234567890'));

        $request = new Request('POST', 'http://localhost', [], '{"shopId": "123", "apiKey": "1", "secretKey": "2"}');

        $response = $this->registerService->registerConfirm($request);

        $shop = $this->shopRepository->getShopFromId('123');
        static::assertNotNull($shop);

        static::assertEquals('1', $shop->getShopClientId());
        static::assertEquals('2', $shop->getShopClientSecret());

        static::assertCount(2, $events);
        static::assertArrayHasKey('0', $events);
        static::assertArrayHasKey('1', $events);
        static::assertInstanceOf(BeforeRegistrationCompletedEvent::class, $events[0]);
        static::assertInstanceOf(RegistrationCompletedEvent::class, $events[1]);
        static::assertSame(204, $response->getStatusCode());
    }

    public function testWithoutEventDispatcher(): void
    {
        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $this->shopRepository,
            $this->createMock(RequestVerifier::class),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger(),
            null
        );

        $this->shopRepository->createShop(new MockShop('123', 'https://foo.com', '1234567890'));

        $request = new Request('POST', 'http://localhost', [], '{"shopId": "123", "apiKey": "1", "secretKey": "2"}');

        $response = $registrationService->registerConfirm($request);

        $shop = $this->shopRepository->getShopFromId('123');
        static::assertNotNull($shop);
        static::assertEquals('1', $shop->getShopClientId());
        static::assertEquals('2', $shop->getShopClientSecret());
        static::assertSame(204, $response->getStatusCode());
    }

    public function testRegisterMessageIsLogged(): void
    {
        $logger = static::createMock(LoggerInterface::class);
        $logger
            ->expects(static::once())
            ->method('info')
            ->with('Shop registration request received', [
                'shop-id' => '123',
                'shop-url' => 'https://my-shop.com',
            ]);

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $this->shopRepository,
            $this->createMock(RequestVerifier::class),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            $logger,
            null
        );

        $request = new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890');
        $registrationService->register($request);
    }

    public function testRegisterConfirmMessageIsLogged(): void
    {
        $logger = static::createMock(LoggerInterface::class);
        $logger
            ->expects(static::once())
            ->method('info')
            ->with('Shop registration confirmed', [
                'shop-id' => '123',
                'shop-url' => 'https://foo.com',
            ]);

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $this->shopRepository,
            $this->createMock(RequestVerifier::class),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            $logger,
            null
        );

        $this->shopRepository->createShop(new MockShop('123', 'https://foo.com', '1234567890'));
        $request = new Request('POST', 'http://localhost', [], '{"shopId": "123", "apiKey": "1", "secretKey": "2"}');

        $registrationService->registerConfirm($request);
    }

    public function testRegisterRequestIsAuthenticated(): void
    {
        $request = new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890');

        $verifier = static::createMock(RequestVerifier::class);
        $verifier
            ->expects(static::once())
            ->method('authenticateRegistrationRequest')
            ->with($request);

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $this->shopRepository,
            $verifier,
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger()
        );

        $registrationService->register($request);
    }

    public function testRegisterConfirmRequestIsAuthenticated(): void
    {
        $request = new Request('POST', 'http://localhost', [], '{"shopId": "123", "apiKey": "1", "secretKey": "2"}');

        $this->shopRepository->createShop(new MockShop('123', 'https://foo.com', '1234567890'));

        $verifier = static::createMock(RequestVerifier::class);
        $verifier
            ->expects(static::once())
            ->method('authenticatePostRequest')
            ->with($request);

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $this->shopRepository,
            $verifier,
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger()
        );

        $registrationService->registerConfirm($request);
    }

    public function testBodyRewindIsCalled(): void
    {
        $body = static::createMock(StreamInterface::class);
        $body
            ->expects(static::once())
            ->method('rewind');

        $body
            ->method('getContents')
            ->willReturn('{"shopId": "123", "apiKey": "1", "secretKey": "2"}');

        $request = new Request('POST', 'http://localhost', []);
        $request = $request->withBody($body);

        $this->shopRepository->createShop(new MockShop('123', 'https://foo.com', '1234567890'));

        $this->registerService->registerConfirm($request);
    }

    /**
     * @dataProvider missingRegisterShopParametersProvider
     * @param array<string, mixed> $params
     */
    public function testRegisterMissingShopParameters(array $params): void
    {
        // Skip test, provider is for another test
        if (\array_key_exists('apiKey', $params) || \array_key_exists('secretKey', $params)) {
            static::assertTrue(true);
            return;
        }

        $query = \http_build_query($params);
        $uri = 'https://localhost.com?' . $query;

        $request = new Request('POST', $uri);
        $registrationService = new RegistrationService(
            $this->appConfiguration,
            new MockShopRepository(),
            static::createMock(RequestVerifier::class),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger()
        );

        $this->expectException(MissingShopParameterException::class);
        $registrationService->register($request);
    }

    /**
     * @dataProvider missingRegisterConfirmShopParametersProvider
     * @param array<string, mixed> $params
     */
    public function testRegisterConfirmMissingShopParameters(array $params): void
    {
        $request = new Request('POST', '/', [], \json_encode($params, \JSON_THROW_ON_ERROR));
        $registrationService = new RegistrationService(
            $this->appConfiguration,
            new MockShopRepository(),
            static::createMock(RequestVerifier::class),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger()
        );

        $this->expectException(MissingShopParameterException::class);
        $registrationService->registerConfirm($request);
    }

    #[DataProvider('shopUrlsProviderForCreation')]
    public function testRegisterCreateShopUrlIsSanitized(
        string $unsanitizedShopUrl,
        string $expectedUrl,
    ): void {
        $shopRepository = $this->createMock(ShopRepositoryInterface::class);

        $expectedShop = new MockShop('123', $expectedUrl, '1234567890');

        $shopRepository
            ->expects(static::once())
            ->method('getShopFromId')
            ->with('123')
            ->willReturn(null);

        $shopRepository
            ->expects(static::once())
            ->method('createShopStruct')
            ->willReturn($expectedShop);

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $shopRepository,
            $this->createMock(RequestVerifier::class),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger(),
            null
        );

        $request = new Request(
            'GET',
            sprintf('http://localhost?shop-id=123&shop-url=%s&timestamp=1234567890', $unsanitizedShopUrl)
        );

        $registrationService->register($request);
    }


    #[DataProvider('shopUrlsProviderForUpdate')]
    public function testRegisterUpdateShopUrlIsSanitized(
        string $oldShopUrl,
        string $newUnsanitizedShopUrl,
        string $expectedUrl,
    ): void {
        $shopRepository = $this->createMock(ShopRepositoryInterface::class);

        $shop = new MockShop('123', $oldShopUrl, '1234567890');

        $shopRepository
            ->expects(static::once())
            ->method('getShopFromId')
            ->willReturn($shop);

        $shopRepository
            ->expects(static::never())
            ->method('createShopStruct');

        $shopRepository
            ->expects(static::once())
            ->method('updateShop')
            ->with($this->callback(function (MockShop $shop) use ($expectedUrl) {
                return $shop->getShopUrl() === $expectedUrl;
            }));

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $shopRepository,
            $this->createMock(RequestVerifier::class),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger(),
            null
        );

        $request = new Request(
            'GET',
            sprintf('http://localhost?shop-id=123&shop-url=%s&timestamp=1234567890', $newUnsanitizedShopUrl)
        );

        $registrationService->register($request);
    }

    /**
     * @return iterable<array<array<string, mixed>>>
     */
    public static function missingRegisterShopParametersProvider(): iterable
    {
        yield [[]];
        yield [['shop-id' => null]];
        yield [['shop-id' => 123]];
        yield [['shop-url' => null]];
        yield [['shop-url' => 'https://my-shop.com']];
        yield [['shop-id' => 123, 'shop-url' => null]];
    }

    /**
     * @return iterable<array<array<string, mixed>>>
     */
    public static function missingRegisterConfirmShopParametersProvider(): iterable
    {
        yield [[]];
        yield [['shopId' => null]];
        yield [['shopId' => 123]];
        yield [['shop-url' => null]];
        yield [['shop-url' => 'https://my-shop.com']];
        yield [['shopId' => 123, 'shop-url' => null]];
        yield [['shopId' => '123', 'shop-url' => 'https://my-shop.com', 'apiKey' => null]];
        yield [['shopId' => '123', 'shop-url' => 'https://my-shop.com', 'apiKey' => 123]];
        yield [['shopId' => '123', 'shop-url' => 'https://my-shop.com', 'secretKey' => null]];
        yield [['shopId' => '123', 'shop-url' => 'https://my-shop.com', 'secretKey' => 123]];
        yield [['apiKey' => 123]];
        yield [['apiKey' => null]];
        yield [['apiKey' => '123', 'secretKey' => null]];
        yield [['apiKey' => '123', 'secretKey' => 123]];
        yield [['shop-id' => '123', 'apiKey' => '123']];
        yield [['shop-id' => '123', 'apiKey' => '123', 'secretKey' => 123]];
        yield [['shop-id' => '', 'apiKey' => '', 'secretKey' => '']];
        yield [['shop-id' => '', 'apiKey' => '', 'secretKey' => '123']];
        yield [['shop-id' => '', 'apiKey' => '', 'secretKey' => '']];
        yield [['shop-id' => '', 'apiKey' => '123', 'secretKey' => '']];
        yield [['shop-id' => '', 'apiKey' => '123', 'secretKey' => '123']];
        yield [['shop-id' => '123', 'apiKey' => '', 'secretKey' => '']];
        yield [['shop-id' => '123', 'apiKey' => '', 'secretKey' => '123']];
        yield [['shop-id' => '123', 'apiKey' => '123', 'secretKey' => '']];
    }

    /**
     * @return iterable<array<string, string|bool>>
     */
    public function shopUrlsProviderForCreation(): iterable
    {
        yield 'Valid URL with port' => [
            'unsanitizedShopUrl' => 'https://my-shop.com:80',
            'expectedUrl' => 'https://my-shop.com:80',
        ];

        yield 'Valid URL with port and trailing slash' => [
            'unsanitizedShopUrl' => 'https://my-shop.com:8080/',
            'expectedUrl' => 'https://my-shop.com:8080',
        ];

        yield 'Valid URL with port, path and trailing slash' => [
            'unsanitizedShopUrl' => 'https://my-shop.com:8080//test/',
            'expectedUrl' => 'https://my-shop.com:8080/test',
        ];

        yield 'Valid URL without trailing slash' => [
            'unsanitizedShopUrl' => 'https://my-shop.com',
            'expectedUrl' => 'https://my-shop.com',
        ];

        yield 'Valid URL with trailing slash' => [
            'unsanitizedShopUrl' => 'https://my-shop.com/',
            'expectedUrl' => 'https://my-shop.com',
        ];

        yield 'Invalid URL with trailing slash' => [
            'unsanitizedShopUrl' => 'https://my-shop.com/test/',
            'expectedUrl' => 'https://my-shop.com/test',
        ];

        yield 'Invalid URL with double slashes' => [
            'unsanitizedShopUrl' => 'https://my-shop.com//test',
            'expectedUrl' => 'https://my-shop.com/test',
        ];

        yield 'Invalid URL with 2 slashes and trailing slash' => [
            'unsanitizedShopUrl' => 'https://my-shop.com//test/',
            'expectedUrl' => 'https://my-shop.com/test',
        ];

        yield 'Invalid URL with 3 slashes and trailing slash' => [
            'unsanitizedShopUrl' => 'https://my-shop.com///test/',
            'expectedUrl' => 'https://my-shop.com/test',
        ];

        yield 'Invalid URL with multiple slashes' => [
            'unsanitizedShopUrl' => 'https://my-shop.com///test/test1//test2',
            'expectedUrl' => 'https://my-shop.com/test/test1/test2',
        ];

        yield 'Invalid URL with multiple slashes and trailing slash' => [
            'unsanitizedShopUrl' => 'https://my-shop.com///test/test1//test2/',
            'expectedUrl' => 'https://my-shop.com/test/test1/test2',
        ];

        yield 'Invalid URL with multiple slashes and multiple trailing slash' => [
            'unsanitizedShopUrl' => 'https://my-shop.com///test/test1//test2//',
            'expectedUrl' => 'https://my-shop.com/test/test1/test2',
        ];
    }

    /**
     * @return iterable<array<string, string|bool>>
     */
    public static function shopUrlsProviderForUpdate(): iterable
    {
        yield 'Valid URL with port' => [
            'oldShopUrl' => 'https://my-shop.com:80',
            'newUnsanitizedShopUrl' => 'https://my-changed-shop.de:80',
            'expectedUrl' => 'https://my-changed-shop.de:80',
        ];

        yield 'Valid URL with port and trailing slash' => [
            'oldShopUrl' => 'https://my-shop.com:8080/',
            'newUnsanitizedShopUrl' => 'https://my-changed-shop.com:8080/',
            'expectedUrl' => 'https://my-changed-shop.com:8080',
        ];
        //
        yield 'Valid URL with port, path and trailing slash' => [
            'oldShopUrl' => 'https://my-shop.com:8080//test/',
            'newUnsanitizedShopUrl' => 'https://my-changed-shop.com:8080//test/',
            'expectedUrl' => 'https://my-changed-shop.com:8080/test',
            'sanitizeShopUrlInDatabase' => true,
        ];

        yield 'Valid URL without trailing slash' => [
            'oldShopUrl' => 'https://my-shop.com',
            'newUnsanitizedShopUrl' => 'https://my-changed-shop.com',
            'expectedUrl' => 'https://my-changed-shop.com',
            'sanitizeShopUrlInDatabase' => false,
        ];

        yield 'Valid URL with trailing slash' => [
            'oldShopUrl' => 'https://my-shop.com/',
            'newUnsanitizedShopUrl' => 'https://my-changed-shop.com/',
            'expectedUrl' => 'https://my-changed-shop.com',
            'sanitizeShopUrlInDatabase' => true,
        ];

        yield 'Invalid URL with trailing slash' => [
            'oldShopUrl' => 'https://my-shop.com/test/',
            'newUnsanitizedShopUrl' => 'https://my-changed-shop.com/test/',
            'expectedUrl' => 'https://my-changed-shop.com/test',
            'sanitizeShopUrlInDatabase' => true,
        ];

        yield 'Invalid URL with double slashes' => [
            'oldShopUrl' => 'https://my-shop.com//test',
            'newUnsanitizedShopUrl' => 'https://my-changed-shop.com//test',
            'expectedUrl' => 'https://my-changed-shop.com/test',
            'sanitizeShopUrlInDatabase' => true,
        ];

        yield 'Invalid URL with 2 slashes and trailing slash' => [
            'oldShopUrl' => 'https://my-shop.com//test/',
            'newUnsanitizedShopUrl' => 'https://my-changed-shop.com//test/',
            'expectedUrl' => 'https://my-changed-shop.com/test',
            'sanitizeShopUrlInDatabase' => true,
        ];

        yield 'Invalid URL with 3 slashes and trailing slash' => [
            'oldShopUrl' => 'https://my-shop.com///test/',
            'newUnsanitizedShopUrl' => 'https://my-changed-shop.com///test/',
            'expectedUrl' => 'https://my-changed-shop.com/test',
            'sanitizeShopUrlInDatabase' => true,
        ];

        yield 'Invalid URL with multiple slashes' => [
            'oldShopUrl' => 'https://my-shop.com///test/test1//test2',
            'newUnsanitizedShopUrl' => 'https://my-changed-shop.com///test/test1//test2',
            'expectedUrl' => 'https://my-changed-shop.com/test/test1/test2',
            'sanitizeShopUrlInDatabase' => true,
        ];

        yield 'Invalid URL with multiple slashes and trailing slash' => [
            'oldShopUrl' => 'https://my-shop.com///test/test1//test2/',
            'newUnsanitizedShopUrl' => 'https://my-changed-shop.com///test/test1//test2/',
            'expectedUrl' => 'https://my-changed-shop.com/test/test1/test2',
            'sanitizeShopUrlInDatabase' => true,
        ];

        yield 'Invalid URL with multiple slashes and multiple trailing slash' => [
            'oldShopUrl' => 'https://my-shop.com///test/test1//test2//',
            'newUnsanitizedShopUrl' => 'https://my-changed-shop.com///test/test1//test2//',
            'expectedUrl' => 'https://my-changed-shop.com/test/test1/test2',
            'sanitizeShopUrlInDatabase' => true,
        ];
    }
}

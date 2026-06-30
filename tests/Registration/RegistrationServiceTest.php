<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Registration;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shopware\App\SDK\AppConfiguration;
use Shopware\App\SDK\Authentication\DualSignatureRequestVerifier;
use Shopware\App\SDK\Authentication\RequestVerifier;
use Shopware\App\SDK\Authentication\ResponseSigner;
use Shopware\App\SDK\Event\BeforeRegistrationCompletedEvent;
use Shopware\App\SDK\Event\BeforeRegistrationStartsEvent;
use Shopware\App\SDK\Event\RegistrationCompletedEvent;
use Shopware\App\SDK\Exception\MissingShopParameterException;
use Shopware\App\SDK\Exception\SignatureInvalidException;
use Shopware\App\SDK\Exception\SignatureNotFoundException;
use Shopware\App\SDK\Exception\ShopNotFoundException;
use Shopware\App\SDK\Registration\RandomStringShopSecretGenerator;
use Shopware\App\SDK\Registration\RegistrationService;
use Shopware\App\SDK\Registration\ShopSecretGeneratorInterface;
use Shopware\App\SDK\Shop\ShopRepositoryInterface;
use Shopware\App\SDK\Test\MockShop;
use Shopware\App\SDK\Test\MockShopRepository;
use Symfony\Component\EventDispatcher\EventDispatcher;

#[CoversClass(RegistrationService::class)]
class RegistrationServiceTest extends TestCase
{
    private RegistrationService $registerService;
    private MockShopRepository $shopRepository;
    private AppConfiguration $appConfiguration;

    protected function setUp(): void
    {
        $this->appConfiguration = new AppConfiguration('My App', 'my-secret', 'http://localhost', true);
        $this->shopRepository = new MockShopRepository();
        $this->registerService = new RegistrationService(
            $this->appConfiguration,
            $this->shopRepository,
            new DualSignatureRequestVerifier($this->createMock(RequestVerifier::class)),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator()
        );
    }

    public function tearDown(): void
    {
        $this->shopRepository->shops = [];
    }

    public function testRegisterMissingParameters(): void
    {
        $request = new Request('GET', 'http://localhost');

        $this->expectException(MissingShopParameterException::class);

        $this->registerService->register($request);
    }

    public function testRegisterThrowsWhenAppSignatureMissing(): void
    {
        $registrationService = new RegistrationService(
            $this->appConfiguration,
            new MockShopRepository(),
            new DualSignatureRequestVerifier(),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger()
        );

        $request = new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890');

        $this->expectException(SignatureNotFoundException::class);
        $registrationService->register($request);
    }

    public function testRegisterThrowsWhenAppSignatureInvalid(): void
    {
        $registrationService = new RegistrationService(
            $this->appConfiguration,
            new MockShopRepository(),
            new DualSignatureRequestVerifier(),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger()
        );

        $query = 'shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890';
        $request = new Request('GET', 'http://localhost?' . $query);
        $request = $request->withHeader('shopware-app-signature', 'invalid-signature');

        $this->expectException(SignatureInvalidException::class);
        $registrationService->register($request);
    }

    public function testRegisterConfirmedShopRequiresShopSignatureWhenEnforced(): void
    {
        $shopRepository = new MockShopRepository();
        $shop = new MockShop('123', 'https://my-shop.com', 'shop-secret');
        $shop->setRegistrationConfirmed();
        $shopRepository->createShop($shop);

        $query = 'shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890';
        $appSignature = hash_hmac('sha256', $query, $this->appConfiguration->getAppSecret());

        $request = new Request('GET', 'http://localhost?' . $query);
        $request = $request->withHeader('shopware-app-signature', $appSignature);

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $shopRepository,
            new DualSignatureRequestVerifier(),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger()
        );

        $this->expectException(SignatureNotFoundException::class);
        $registrationService->register($request);
    }

    public function testRegisterUpdateResponseSecretMatchesPendingSecret(): void
    {
        $shopRepository = new MockShopRepository();
        $shopRepository->createShop(new MockShop('123', 'https://my-shop.com', 'existing-secret'));

        $secretGenerator = new class () implements ShopSecretGeneratorInterface {
            public function generate(): string
            {
                return 'fixed-secret';
            }
        };

        $query = 'shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890';
        $appSignature = hash_hmac('sha256', $query, $this->appConfiguration->getAppSecret());

        $request = new Request('GET', 'http://localhost?' . $query);
        $request = $request->withHeader('shopware-app-signature', $appSignature);

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $shopRepository,
            new DualSignatureRequestVerifier(),
            new ResponseSigner(),
            $secretGenerator,
            new NullLogger()
        );

        $response = $registrationService->register($request);
        $json = json_decode($response->getBody()->getContents(), true);

        $updatedShop = $shopRepository->getShopFromId('123');
        static::assertNotNull($updatedShop);
        static::assertSame('fixed-secret', $json['secret'] ?? null);
        static::assertSame('fixed-secret', $updatedShop->getPendingShopSecret());
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
            new DualSignatureRequestVerifier($this->createMock(RequestVerifier::class)),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger(),
            $eventDispatcher
        );

        $shopRepository
            ->expects(static::once())
            ->method('getShopFromId')
            ->willReturn(null);

        $shop = null;

        $shopRepository
            ->expects(static::once())
            ->method('createShopStruct')
            ->willReturnCallback(function (string $shopId, string $shopUrl, string $secret) use (&$shop): MockShop {
                $shop = new MockShop($shopId, $shopUrl, $secret);

                return $shop;
            });

        $shopRepository
            ->expects(static::never())
            ->method('updateShop');

        $eventDispatcher
            ->expects(static::once())
            ->method('dispatch');

        $response = $registrationService->register(
            new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890')
        );

        static::assertSame(200, $response->getStatusCode());
        $json = json_decode((string)$response->getBody()->getContents(), true);

        static::assertCount(1, $events);
        static::assertInstanceOf(BeforeRegistrationStartsEvent::class, $events[0]);

        static::assertIsArray($json);
        static::assertArrayHasKey('proof', $json);
        static::assertArrayHasKey('confirmation_url', $json);
        static::assertArrayHasKey('secret', $json);
        static::assertNotNull($shop);
        static::assertSame('https://my-shop.com', $shop->getShopUrl());
        static::assertSame($json['secret'], $shop->getShopSecret());
        static::assertSame('https://my-shop.com', $shop->getPendingShopUrl());
        static::assertSame($json['secret'], $shop->getPendingShopSecret());
        static::assertFalse($shop->isRegistrationConfirmed());
    }

    public function testRegisterCreateMustNotDispatchBeforeRegistrationStartsEvent(): void
    {
        $shopRepository = $this->createMock(ShopRepositoryInterface::class);

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $shopRepository,
            new DualSignatureRequestVerifier($this->createMock(RequestVerifier::class)),
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
            new DualSignatureRequestVerifier($this->createMock(RequestVerifier::class)),
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
            new DualSignatureRequestVerifier($this->createMock(RequestVerifier::class)),
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
            new DualSignatureRequestVerifier($this->createMock(RequestVerifier::class)),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger(),
            $eventDispatcher
        );

        $shop = new MockShop('123', 'https://foo.com', '1234567890');
        $shop->setPendingShopSecret('1234567890');
        $shop->setPendingShopUrl('https://foo.com');
        $this->shopRepository->createShop($shop);

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
            new DualSignatureRequestVerifier($this->createMock(RequestVerifier::class)),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger(),
            null
        );

        $shop = new MockShop('123', 'https://foo.com', '1234567890');
        $shop->setPendingShopSecret('1234567890');
        $shop->setPendingShopUrl('https://foo.com');
        $this->shopRepository->createShop($shop);

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
        // register() logs "started" then "request received"; assert the second.
        $logger = $this->createMock(LoggerInterface::class);
        $matcher = $this->exactly(2);
        $logger->expects($matcher)
            ->method('info')
            ->willReturnCallback(function (string $message, array $context) use ($matcher): void {
                if ($matcher->numberOfInvocations() !== 2) {
                    return;
                }

                static::assertSame('Shop registration request received', $message);
                static::assertSame('123', $context['shop-id']);
                static::assertSame('https://my-shop.com', $context['shop-url']);
                static::assertSame('https://my-shop.com', $context['signed-shop-url']);
                static::assertSame('6.6.10.0', $context['shopware-version']);
                // The signed payload is shop-id + shop-url + app name, in that order.
                static::assertStringStartsWith('123https://my-shop.com', $context['signature-payload']);
            });

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $this->shopRepository,
            new DualSignatureRequestVerifier($this->createMock(RequestVerifier::class)),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            $logger,
            null
        );

        $request = (new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890'))
            ->withHeader('sw-version', '6.6.10.0');
        $registrationService->register($request);
    }

    public function testRegisterConfirmMessageIsLogged(): void
    {
        // registerConfirm() logs "confirmation started" then "confirmed" (no rotation here); assert the second.
        $logger = $this->createMock(LoggerInterface::class);
        $matcher = $this->exactly(2);
        $logger->expects($matcher)
            ->method('info')
            ->willReturnCallback(function (string $message, array $context) use ($matcher): void {
                if ($matcher->numberOfInvocations() !== 2) {
                    return;
                }

                static::assertSame('Shop registration confirmed', $message);
                static::assertSame('123', $context['shop-id']);
                static::assertSame('https://my-shop.com', $context['shop-url']);
                static::assertSame('6.6.10.0', $context['shopware-version']);
            });

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $this->shopRepository,
            new DualSignatureRequestVerifier($this->createMock(RequestVerifier::class)),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            $logger,
            null
        );

        $shop = new MockShop('123', 'https://foo.com', '1234567890');
        $shop->setPendingShopSecret('1234567890');
        $shop->setPendingShopUrl('https://my-shop.com');
        $this->shopRepository->createShop($shop);
        $request = (new Request('POST', 'http://localhost', [], '{"shopId": "123", "apiKey": "1", "secretKey": "2"}'))
            ->withHeader('sw-version', '6.6.10.0');

        $registrationService->registerConfirm($request);
    }

    public function testRegisterConfirmRotatesTheSecretAndLogsForAReRegistration(): void
    {
        // A re-registration confirm logs "confirmation started", then "Shop secret rotated", then "confirmed".
        $logger = $this->createMock(LoggerInterface::class);
        $matcher = $this->exactly(3);
        $logger->expects($matcher)
            ->method('info')
            ->willReturnCallback(function (string $message, array $context) use ($matcher): void {
                if ($matcher->numberOfInvocations() !== 2) {
                    return;
                }

                static::assertSame('Shop secret rotated during registration confirmation', $message);
                static::assertSame('123', $context['shop-id']);
                static::assertTrue($context['has-previous-secret']);
            });

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $this->shopRepository,
            new DualSignatureRequestVerifier($this->createMock(RequestVerifier::class)),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            $logger,
            null
        );

        // An already-confirmed shop whose pending secret differs from the active one: confirming it rotates.
        $shop = new MockShop('123', 'https://foo.com', 'current-secret');
        $shop->setPendingShopSecret('rotated-in-secret')
            ->setPendingShopUrl('https://my-shop.com')
            ->setRegistrationConfirmed();
        $this->shopRepository->createShop($shop);

        $request = (new Request('POST', 'http://localhost', [], '{"shopId": "123", "apiKey": "1", "secretKey": "2"}'))
            ->withHeader('sw-version', '6.6.10.0');

        $registrationService->registerConfirm($request);

        // The pending secret becomes the active one; the old active secret is kept as the previous secret.
        $rotated = $this->shopRepository->getShopFromId('123');
        static::assertNotNull($rotated);
        static::assertSame('rotated-in-secret', $rotated->getShopSecret());
        static::assertSame('current-secret', $rotated->getPreviousShopSecret());
        static::assertNotNull($rotated->getSecretsRotatedAt());
    }

    public function testRegisterStartedLogIncludesDoubleSignatureContext(): void
    {
        // "Shop registration started" is the first info log register() emits.
        $logger = $this->createMock(LoggerInterface::class);
        $matcher = $this->exactly(2);
        $logger->expects($matcher)
            ->method('info')
            ->willReturnCallback(function (string $message, array $context) use ($matcher): void {
                if ($matcher->numberOfInvocations() !== 1) {
                    return;
                }

                static::assertSame('Shop registration started', $message);
                // appConfiguration in setUp() enforces double signature; no shop exists yet, so shop-derived flags are null/false.
                static::assertFalse($context['shop-exists']);
                static::assertTrue($context['enforce-double-signature']);
                static::assertNull($context['has-verified-with-double-signature']);
                static::assertNull($context['has-previous-secret']);
                static::assertSame('6.6.10.0', $context['shopware-version']);
            });

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $this->shopRepository,
            new DualSignatureRequestVerifier($this->createMock(RequestVerifier::class)),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            $logger,
            null
        );

        $request = (new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890'))
            ->withHeader('sw-version', '6.6.10.0');
        $registrationService->register($request);
    }

    public function testRegisterLogsWarningAndRethrowsOnSignatureFailure(): void
    {
        $query = 'shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890';
        $request = new Request('GET', 'http://localhost?' . $query);
        $request = $request->withHeader('shopware-app-signature', 'invalid-signature')
            ->withHeader('sw-version', '6.6.10.0');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with('Shop registration signature verification failed', static::callback(function (array $context): bool {
                static::assertSame('123', $context['shop-id']);
                static::assertSame('https://my-shop.com', $context['shop-url']);
                static::assertSame(SignatureInvalidException::class, $context['exception']);
                // The app signature is the first leg verified, so it is the failing stage here.
                static::assertSame('app-signature', $context['verification-stage']);
                static::assertArrayHasKey('enforce-double-signature', $context);
                static::assertSame('6.6.10.0', $context['shopware-version']);

                return true;
            }));

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $this->shopRepository,
            new DualSignatureRequestVerifier(),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            $logger,
            null
        );

        $this->expectException(SignatureInvalidException::class);
        $registrationService->register($request);
    }

    public function testRegisterConfirmLogsWarningAndRethrowsOnSignatureFailure(): void
    {
        $shop = new MockShop('123', 'https://my-shop.com', 'current-secret');
        // No pending secret set -> confirmation verification throws SignatureInvalidException.
        $this->shopRepository->createShop($shop);

        $request = (new Request('POST', 'http://localhost', [], '{"shopId": "123", "apiKey": "1", "secretKey": "2"}'))
            ->withHeader('sw-version', '6.6.10.0');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with('Shop registration confirmation signature verification failed', static::callback(function (array $context): bool {
                static::assertSame('123', $context['shop-id']);
                static::assertSame(SignatureInvalidException::class, $context['exception']);
                // No pending secret was ever stored, so confirmation fails at that stage.
                static::assertSame('missing-pending-secret', $context['verification-stage']);
                static::assertSame('6.6.10.0', $context['shopware-version']);

                return true;
            }));

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $this->shopRepository,
            new DualSignatureRequestVerifier(),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            $logger,
            null
        );

        $this->expectException(SignatureInvalidException::class);
        $registrationService->registerConfirm($request);
    }

    public function testRegisterLogsNotFoundStageWhenAppSignatureMissing(): void
    {
        // No shopware-app-signature header -> the app-signature leg raises SignatureNotFoundException, not Invalid.
        $request = new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with('Shop registration signature verification failed', static::callback(function (array $context): bool {
                static::assertSame(SignatureNotFoundException::class, $context['exception']);
                static::assertSame('app-signature', $context['verification-stage']);

                return true;
            }));

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $this->shopRepository,
            new DualSignatureRequestVerifier(),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            $logger,
            null
        );

        $this->expectException(SignatureNotFoundException::class);
        $registrationService->register($request);
    }

    public function testRegisterConfirmLogsNotFoundStageWhenPendingSignatureMissing(): void
    {
        $shop = new MockShop('123', 'https://my-shop.com', 'current-secret');
        $shop->setPendingShopSecret('pending-secret');
        $this->shopRepository->createShop($shop);

        // Pending secret is set, but the request carries no shop signature -> the pending-secret leg raises NotFound.
        $request = new Request('POST', 'http://localhost', [], '{"shopId": "123", "apiKey": "1", "secretKey": "2"}');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with('Shop registration confirmation signature verification failed', static::callback(function (array $context): bool {
                static::assertSame(SignatureNotFoundException::class, $context['exception']);
                static::assertSame('pending-secret', $context['verification-stage']);

                return true;
            }));

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $this->shopRepository,
            new DualSignatureRequestVerifier(),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            $logger,
            null
        );

        $this->expectException(SignatureNotFoundException::class);
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
            new DualSignatureRequestVerifier($verifier),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger()
        );

        $registrationService->register($request);
    }

    public function testRegisterDoesNotRequireShopSignatureWhenRegistrationNotConfirmed(): void
    {
        $shop = new MockShop('123', 'https://my-shop.com', 'existing-secret');
        $this->shopRepository->createShop($shop);

        $query = 'shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890';
        $signature = hash_hmac('sha256', $query, $this->appConfiguration->getAppSecret());

        $request = new Request('GET', 'http://localhost?' . $query);
        $request = $request->withHeader('shopware-app-signature', $signature);

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $this->shopRepository,
            new DualSignatureRequestVerifier(new RequestVerifier()),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger()
        );

        $response = $registrationService->register($request);

        $updatedShop = $this->shopRepository->getShopFromId('123');
        static::assertNotNull($updatedShop);
        static::assertSame('existing-secret', $updatedShop->getShopSecret());
        static::assertNotNull($updatedShop->getPendingShopSecret());
        static::assertFalse($updatedShop->isRegistrationConfirmed());
        static::assertSame(200, $response->getStatusCode());
    }

    public function testRegisterConfirmRequestIsAuthenticated(): void
    {
        $request = new Request('POST', 'http://localhost', [], '{"shopId": "123", "apiKey": "1", "secretKey": "2"}');

        $shop = new MockShop('123', 'https://foo.com', '1234567890');
        $shop->setPendingShopSecret('1234567890');
        $shop->setPendingShopUrl('https://my-shop.com');
        $this->shopRepository->createShop($shop);

        $verifier = static::createMock(RequestVerifier::class);
        $verifier
            ->expects(static::once())
            ->method('authenticatePostRequest')
            ->with($request, '1234567890');

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $this->shopRepository,
            new DualSignatureRequestVerifier($verifier),
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

        $shop = new MockShop('123', 'https://foo.com', '1234567890');
        $shop->setPendingShopSecret('1234567890');
        $shop->setPendingShopUrl('https://my-shop.com');
        $this->shopRepository->createShop($shop);

        $this->registerService->registerConfirm($request);
    }

    public function testRegisterConfirmWithPendingUrlButNoPendingSecretThrows(): void
    {
        $shopRepository = new MockShopRepository();
        $shop = new MockShop('123', 'https://foo.com', 'secret');
        $shop->setPendingShopUrl('https://new-url.com//path/');
        $shopRepository->createShop($shop);

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $shopRepository,
            new DualSignatureRequestVerifier(static::createMock(RequestVerifier::class)),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger()
        );

        $request = new Request('POST', 'http://localhost', [], '{"shopId": "123", "apiKey": "1", "secretKey": "2"}');

        $this->expectException(SignatureInvalidException::class);
        $registrationService->registerConfirm($request);
    }

    public function testRegisterConfirmDoesNotRequirePreviousSignatureWhenNotEnforced(): void
    {
        $shopRepository = new MockShopRepository();
        $shop = new MockShop('123', 'https://foo.com', 'old-secret');
        $shop->setPendingShopSecret('new-secret')
            ->setRegistrationConfirmed();
        $shop->setPendingShopUrl('https://foo.com');
        $shopRepository->createShop($shop);

        $body = '{"shopId":"123","apiKey":"1","secretKey":"2"}';
        $request = new Request('POST', 'http://localhost', [], $body);
        $request = $request->withHeader('shopware-shop-signature', hash_hmac('sha256', $body, 'new-secret'));

        $verifier = static::createMock(RequestVerifier::class);
        $verifier
            ->expects(static::once())
            ->method('authenticatePostRequest')
            ->with($request, 'new-secret');

        $registrationService = new RegistrationService(
            new AppConfiguration('My App', 'my-secret', 'http://localhost', enforceDoubleSignature: false),
            $shopRepository,
            new DualSignatureRequestVerifier($verifier),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger()
        );

        $registrationService->registerConfirm($request);
    }

    public function testRegisterThenConfirmAppliesPendingUrlForNewShop(): void
    {
        $shopRepository = new MockShopRepository();
        $secretGenerator = new class () implements ShopSecretGeneratorInterface {
            public function generate(): string
            {
                return 'fixed-secret';
            }
        };

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $shopRepository,
            new DualSignatureRequestVerifier(),
            new ResponseSigner(),
            $secretGenerator,
            new NullLogger()
        );

        $query = 'shop-id=123&shop-url=https://my-shop.com//path/&timestamp=1234567890';
        $appSignature = hash_hmac('sha256', $query, $this->appConfiguration->getAppSecret());

        $registerRequest = new Request('GET', 'http://localhost?' . $query);
        $registerRequest = $registerRequest->withHeader('shopware-app-signature', $appSignature);

        $registrationService->register($registerRequest);

        $shop = $shopRepository->getShopFromId('123');
        static::assertNotNull($shop);
        static::assertSame('https://my-shop.com//path/', $shop->getPendingShopUrl());

        $body = '{"shopId":"123","apiKey":"1","secretKey":"2"}';
        $confirmRequest = new Request('POST', 'http://localhost', [], $body);
        $confirmRequest = $confirmRequest->withHeader('shopware-shop-signature', hash_hmac('sha256', $body, 'fixed-secret'));

        $registrationService->registerConfirm($confirmRequest);

        $shop = $shopRepository->getShopFromId('123');
        static::assertNotNull($shop);
        static::assertSame('https://my-shop.com/path/', $shop->getShopUrl());
        static::assertNull($shop->getPendingShopUrl());
    }

    public function testRegisterConfirmCallsUpdateShop(): void
    {
        $shop = new MockShop('123', 'https://foo.com', 'old-secret');
        $shop->setPendingShopSecret('new-secret');
        $shop->setPendingShopUrl('https://foo.com');

        $shopRepository = $this->createMock(ShopRepositoryInterface::class);
        $shopRepository
            ->expects(static::once())
            ->method('getShopFromId')
            ->with('123')
            ->willReturn($shop);

        $shopRepository
            ->expects(static::once())
            ->method('updateShop')
            ->with(static::callback(function (MockShop $updatedShop): bool {
                return $updatedShop->getShopSecret() === 'new-secret'
                    && $updatedShop->getPreviousShopSecret() === 'old-secret'
                    && $updatedShop->isRegistrationConfirmed();
            }));

        $verifier = static::createMock(RequestVerifier::class);
        $verifier
            ->expects(static::once())
            ->method('authenticatePostRequest');

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $shopRepository,
            new DualSignatureRequestVerifier($verifier),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger()
        );

        $request = new Request('POST', 'http://localhost', [], '{"shopId": "123", "apiKey": "1", "secretKey": "2"}');
        $registrationService->registerConfirm($request);
    }

    /**
     * @param array<string, mixed> $params
     */
    #[DataProvider('missingRegisterShopParametersProvider')]
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
            new DualSignatureRequestVerifier(static::createMock(RequestVerifier::class)),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger()
        );

        $this->expectException(MissingShopParameterException::class);
        $registrationService->register($request);
    }

    /**
     * @param array<string, mixed> $params
     */
    #[DataProvider('missingRegisterConfirmShopParametersProvider')]
    public function testRegisterConfirmMissingShopParameters(array $params): void
    {
        $request = new Request('POST', '/', [], \json_encode($params, \JSON_THROW_ON_ERROR));
        $registrationService = new RegistrationService(
            $this->appConfiguration,
            new MockShopRepository(),
            new DualSignatureRequestVerifier(static::createMock(RequestVerifier::class)),
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
            new DualSignatureRequestVerifier($this->createMock(RequestVerifier::class)),
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
            ->with($this->callback(function (MockShop $shop) use ($oldShopUrl, $newUnsanitizedShopUrl, $expectedUrl) {
                // During update registration:
                // - the shop URL gets sanitized
                // - the new URL is stored in pendingShopUrl (also sanitized now)
                // - a new secret is generated and stored in pendingShopSecret

                // Sanitize the old URL to compare
                $uri = new \Nyholm\Psr7\Uri($oldShopUrl);
                $path = preg_replace('#/{2,}#', '/', $uri->getPath()) ?? '';
                $uri = $uri->withPath($path);
                $sanitizedOldUrl = (string)$uri;

                // Sanitize the new URL to compare
                $uri = new \Nyholm\Psr7\Uri($newUnsanitizedShopUrl);
                $path = preg_replace('#/{2,}#', '/', $uri->getPath()) ?? '';
                $uri = $uri->withPath($path);
                $sanitizedNewUrl = (string)$uri;

                return $shop->getShopUrl() === $sanitizedOldUrl
                    && $shop->getPendingShopUrl() === $sanitizedNewUrl
                    && $shop->getPendingShopSecret() !== null;
            }));

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $shopRepository,
            new DualSignatureRequestVerifier($this->createMock(RequestVerifier::class)),
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
            'expectedUrl' => 'https://my-shop.com:8080/',
        ];

        yield 'Valid URL with port, path and trailing slash' => [
            'unsanitizedShopUrl' => 'https://my-shop.com:8080//test/',
            'expectedUrl' => 'https://my-shop.com:8080/test/',
        ];

        yield 'Valid URL without trailing slash' => [
            'unsanitizedShopUrl' => 'https://my-shop.com',
            'expectedUrl' => 'https://my-shop.com',
        ];

        yield 'Valid URL with trailing slash' => [
            'unsanitizedShopUrl' => 'https://my-shop.com/',
            'expectedUrl' => 'https://my-shop.com/',
        ];

        yield 'Valid URL with trailing slash and subfolder' => [
            'unsanitizedShopUrl' => 'https://my-shop.com/test/',
            'expectedUrl' => 'https://my-shop.com/test',
        ];

        yield 'Invalid URL with double slashes' => [
            'unsanitizedShopUrl' => 'https://my-shop.com//test',
            'expectedUrl' => 'https://my-shop.com/test',
        ];

        yield 'Invalid URL with 2 slashes and trailing slash' => [
            'unsanitizedShopUrl' => 'https://my-shop.com//test/',
            'expectedUrl' => 'https://my-shop.com/test/',
        ];

        yield 'Invalid URL with 3 slashes and trailing slash' => [
            'unsanitizedShopUrl' => 'https://my-shop.com///test/',
            'expectedUrl' => 'https://my-shop.com/test/',
        ];

        yield 'Invalid URL with multiple slashes' => [
            'unsanitizedShopUrl' => 'https://my-shop.com///test/test1//test2',
            'expectedUrl' => 'https://my-shop.com/test/test1/test2',
        ];

        yield 'Invalid URL with multiple slashes and trailing slash' => [
            'unsanitizedShopUrl' => 'https://my-shop.com///test/test1//test2/',
            'expectedUrl' => 'https://my-shop.com/test/test1/test2/',
        ];

        yield 'Invalid URL with multiple slashes and multiple trailing slash' => [
            'unsanitizedShopUrl' => 'https://my-shop.com///test/test1//test2//',
            'expectedUrl' => 'https://my-shop.com/test/test1/test2/',
        ];
    }

    public function testRegisterConfirmWithSecretRotation(): void
    {
        $shopRepository = new MockShopRepository();
        $shop = new MockShop('123', 'https://foo.com', 'old-secret');
        $shop->setRegistrationConfirmed();
        $shop->setPendingShopSecret('new-secret');
        $shop->setPendingShopUrl('https://foo.com');

        $shopRepository->createShop($shop);

        $verifier = static::createMock(RequestVerifier::class);

        // Expect authentication with NEW pending secret using standard header
        $verifier
            ->expects(static::exactly(2))
            ->method('authenticatePostRequest')
            ->willReturnCallback(function ($request, $secret, $header = 'shopware-shop-signature') use ($shop) {
                static::assertContains($secret, ['new-secret', 'old-secret']);
                if ($secret === 'old-secret') {
                    static::assertEquals('shopware-shop-signature-previous', $header);
                } else {
                    static::assertEquals('shopware-shop-signature', $header);
                }
            });

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $shopRepository,
            new DualSignatureRequestVerifier($verifier),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger()
        );

        $request = new Request('POST', 'http://localhost', [], '{"shopId": "123", "apiKey": "1", "secretKey": "2"}');
        $response = $registrationService->registerConfirm($request);

        $shop = $shopRepository->getShopFromId('123');
        static::assertNotNull($shop);

        // After confirmation, secrets should be rotated
        static::assertEquals('new-secret', $shop->getShopSecret());
        static::assertEquals('old-secret', $shop->getPreviousShopSecret());
        static::assertNull($shop->getPendingShopSecret());
        static::assertInstanceOf(\DateTimeImmutable::class, $shop->getSecretsRotatedAt());
        static::assertTrue($shop->isRegistrationConfirmed());
        static::assertSame(204, $response->getStatusCode());
    }

    public function testRegisterConfirmWithoutPendingSecret(): void
    {
        $shopRepository = new MockShopRepository();
        $shop = new MockShop('123', 'https://foo.com', 'current-secret');
        $shopRepository->createShop($shop);

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $shopRepository,
            new DualSignatureRequestVerifier(static::createMock(RequestVerifier::class)),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger()
        );

        $request = new Request('POST', 'http://localhost', [], '{"shopId": "123", "apiKey": "1", "secretKey": "2"}');

        $this->expectException(SignatureInvalidException::class);
        $registrationService->registerConfirm($request);
    }

    public function testRegisterConfirmInitialRegistrationMarksConfirmed(): void
    {
        $shopRepository = new MockShopRepository();
        $shop = new MockShop('123', 'https://foo.com', 'initial-secret');
        $shop->setPendingShopSecret('initial-secret');
        $shop->setPendingShopUrl('https://foo.com');

        $shopRepository->createShop($shop);

        $verifier = static::createMock(RequestVerifier::class);
        $verifier
            ->expects(static::once())
            ->method('authenticatePostRequest')
            ->with(static::anything(), 'initial-secret');

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $shopRepository,
            new DualSignatureRequestVerifier($verifier),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger()
        );

        $request = new Request('POST', 'http://localhost', [], '{"shopId": "123", "apiKey": "1", "secretKey": "2"}');
        $response = $registrationService->registerConfirm($request);

        $shop = $shopRepository->getShopFromId('123');
        static::assertNotNull($shop);
        static::assertSame('initial-secret', $shop->getShopSecret());
        static::assertNull($shop->getPendingShopSecret());
        static::assertNull($shop->getPendingShopUrl());
        static::assertNull($shop->getPreviousShopSecret());
        static::assertNull($shop->getSecretsRotatedAt());
        static::assertTrue($shop->isRegistrationConfirmed());
        static::assertSame(204, $response->getStatusCode());
    }

    public function testRegisterConfirmWithPendingUrlRotation(): void
    {
        $shopRepository = new MockShopRepository();
        $shop = new MockShop('123', 'https://old-url.com', 'secret');
        $shop->setPendingShopSecret('secret');
        $shop->setPendingShopUrl('https://new-url.com//path/');

        $shopRepository->createShop($shop);

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $shopRepository,
            new DualSignatureRequestVerifier(static::createMock(RequestVerifier::class)),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger()
        );

        $request = new Request('POST', 'http://localhost', [], '{"shopId": "123", "apiKey": "1", "secretKey": "2"}');
        $registrationService->registerConfirm($request);

        $shop = $shopRepository->getShopFromId('123');
        static::assertNotNull($shop);

        // URL should be updated and sanitized
        static::assertEquals('https://new-url.com/path/', $shop->getShopUrl());
        static::assertNull($shop->getPendingShopUrl());
    }

    public function testRegisterConfirmWithBothSecretAndUrlRotation(): void
    {
        $shopRepository = new MockShopRepository();
        $shop = new MockShop('123', 'https://old-url.com', 'old-secret');
        $shop->setRegistrationConfirmed();
        $shop->setPendingShopSecret('new-secret')
            ->setPendingShopUrl('https://new-url.com///multiple//slashes/');

        $shopRepository->createShop($shop);

        $verifier = static::createMock(RequestVerifier::class);
        $verifier
            ->expects(static::exactly(2))
            ->method('authenticatePostRequest');

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $shopRepository,
            new DualSignatureRequestVerifier($verifier),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger()
        );

        $request = new Request('POST', 'http://localhost', [], '{"shopId": "123", "apiKey": "1", "secretKey": "2"}');
        $registrationService->registerConfirm($request);

        $shop = $shopRepository->getShopFromId('123');
        static::assertNotNull($shop);

        // Both secret and URL should be rotated
        static::assertEquals('new-secret', $shop->getShopSecret());
        static::assertEquals('old-secret', $shop->getPreviousShopSecret());
        static::assertEquals('https://new-url.com/multiple/slashes/', $shop->getShopUrl());
        static::assertNull($shop->getPendingShopSecret());
        static::assertNull($shop->getPendingShopUrl());
        static::assertInstanceOf(\DateTimeImmutable::class, $shop->getSecretsRotatedAt());
        static::assertTrue($shop->isRegistrationConfirmed());
    }

    public function testRegisterUpdateSetsPendingShopUrl(): void
    {
        $shop = new MockShop('123', 'https://old-url.com', 'secret');

        $shopRepository = static::createMock(ShopRepositoryInterface::class);
        $shopRepository
            ->expects(static::once())
            ->method('getShopFromId')
            ->with('123')
            ->willReturn($shop);

        $shopRepository
            ->expects(static::once())
            ->method('updateShop')
            ->with(static::callback(function (MockShop $shop): bool {
                // During update, the new URL should be in pendingShopUrl (sanitized)
                return $shop->getShopUrl() === 'https://old-url.com'
                    && $shop->getPendingShopUrl() === 'https://new-url.com/path/'
                    && $shop->getPendingShopSecret() !== null;
            }));

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $shopRepository,
            new DualSignatureRequestVerifier(static::createMock(RequestVerifier::class)),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger()
        );

        $request = new Request('GET', 'http://localhost?shop-id=123&shop-url=https://new-url.com//path/&timestamp=1234567890');
        $registrationService->register($request);
    }

    public function testNewShopIsRecordedAsHavingUsedDoubleSignatureVerificationWhenEnforced(): void
    {
        $registerService = new RegistrationService(
            new AppConfiguration('My App', 'my-secret', 'http://localhost', enforceDoubleSignature: true),
            $this->shopRepository,
            new DualSignatureRequestVerifier($this->createMock(RequestVerifier::class)),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator()
        );

        $response = $registerService->register(
            new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890')
        );

        static::assertSame(200, $response->getStatusCode());

        $shop = $this->shopRepository->getShopFromId('123');
        static::assertNotNull($shop);
        static::assertSame('https://my-shop.com', $shop->getShopUrl());
        static::assertTrue($shop->hasVerifiedWithDoubleSignature());
    }

    public function testExistingShopIsRecordedAsHavingUsedDoubleSignatureVerificationWhenEnforced(): void
    {
        $shop = new MockShop('123', 'https://my-shop.com', 'secret', hasVerifiedWithDoubleSignature: false);

        $this->shopRepository->createShop($shop);

        $registerService = new RegistrationService(
            new AppConfiguration('My App', 'my-secret', 'http://localhost', enforceDoubleSignature: true),
            $this->shopRepository,
            new DualSignatureRequestVerifier($this->createMock(RequestVerifier::class)),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator()
        );

        $response = $registerService->register(
            new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890')
        );

        static::assertSame(200, $response->getStatusCode());

        $shop = $this->shopRepository->getShopFromId('123');
        static::assertNotNull($shop);
        static::assertSame('https://my-shop.com', $shop->getShopUrl());
        static::assertTrue($shop->hasVerifiedWithDoubleSignature());
    }

    public function testNewShopIsNotRecordedAsHavingUsedDoubleSignatureVerificationWhenNotEnforced(): void
    {
        $registerService = new RegistrationService(
            new AppConfiguration('My App', 'my-secret', 'http://localhost', enforceDoubleSignature: false),
            $this->shopRepository,
            new DualSignatureRequestVerifier($this->createMock(RequestVerifier::class)),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator()
        );

        $response = $registerService->register(
            new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890')
        );

        static::assertSame(200, $response->getStatusCode());

        $shop = $this->shopRepository->getShopFromId('123');
        static::assertNotNull($shop);
        static::assertSame('https://my-shop.com', $shop->getShopUrl());
        static::assertFalse($shop->hasVerifiedWithDoubleSignature());
    }

    public function testExistingShopIsNotRecordedAsHavingUsedDoubleSignatureVerificationWhenNotEnforced(): void
    {
        $shop = new MockShop('123', 'https://my-shop.com', 'secret', hasVerifiedWithDoubleSignature: false);

        $this->shopRepository->createShop($shop);

        $registerService = new RegistrationService(
            new AppConfiguration('My App', 'my-secret', 'http://localhost', enforceDoubleSignature: false),
            $this->shopRepository,
            new DualSignatureRequestVerifier($this->createMock(RequestVerifier::class)),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator()
        );

        $response = $registerService->register(
            new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890')
        );

        static::assertSame(200, $response->getStatusCode());

        $shop = $this->shopRepository->getShopFromId('123');
        static::assertNotNull($shop);
        static::assertSame('https://my-shop.com', $shop->getShopUrl());
        static::assertFalse($shop->hasVerifiedWithDoubleSignature());
    }

    public function testExistingShopIsRecordedAsHavingUsedDoubleSignatureVerificationWhenHeadersPresent(): void
    {
        $shop = new MockShop('123', 'https://my-shop.com', 'secret', hasVerifiedWithDoubleSignature: false);

        $this->shopRepository->createShop($shop);

        $registerService = new RegistrationService(
            new AppConfiguration('My App', 'my-secret', 'http://localhost', enforceDoubleSignature: false),
            $this->shopRepository,
            new DualSignatureRequestVerifier($this->createMock(RequestVerifier::class)),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator()
        );

        $response = $registerService->register(
            new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890', [
                RequestVerifier::SHOPWARE_SHOP_SIGNATURE_HEADER => 'shopware-shop-signature'
            ])
        );

        static::assertSame(200, $response->getStatusCode());

        $shop = $this->shopRepository->getShopFromId('123');
        static::assertNotNull($shop);
        static::assertSame('https://my-shop.com', $shop->getShopUrl());
        static::assertTrue($shop->hasVerifiedWithDoubleSignature());
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
            'expectedUrl' => 'https://my-changed-shop.com:8080/',
        ];

        yield 'Valid URL with port, path and trailing slash' => [
            'oldShopUrl' => 'https://my-shop.com:8080//test/',
            'newUnsanitizedShopUrl' => 'https://my-changed-shop.com:8080//test/',
            'expectedUrl' => 'https://my-changed-shop.com:8080/test/',
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
            'expectedUrl' => 'https://my-changed-shop.com/',
            'sanitizeShopUrlInDatabase' => true,
        ];

        yield 'Valid URL with trailing slash and subfolder' => [
            'oldShopUrl' => 'https://my-shop.com/test/',
            'newUnsanitizedShopUrl' => 'https://my-changed-shop.com/test/',
            'expectedUrl' => 'https://my-changed-shop.com/test/',
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
            'expectedUrl' => 'https://my-changed-shop.com/test/',
            'sanitizeShopUrlInDatabase' => true,
        ];

        yield 'Invalid URL with 3 slashes and trailing slash' => [
            'oldShopUrl' => 'https://my-shop.com///test/',
            'newUnsanitizedShopUrl' => 'https://my-changed-shop.com///test/',
            'expectedUrl' => 'https://my-changed-shop.com/test/',
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
            'expectedUrl' => 'https://my-changed-shop.com/test/test1/test2/',
            'sanitizeShopUrlInDatabase' => true,
        ];

        yield 'Invalid URL with multiple slashes and multiple trailing slash' => [
            'oldShopUrl' => 'https://my-shop.com///test/test1//test2//',
            'newUnsanitizedShopUrl' => 'https://my-changed-shop.com///test/test1//test2//',
            'expectedUrl' => 'https://my-changed-shop.com/test/test1/test2/',
            'sanitizeShopUrlInDatabase' => true,
        ];
    }

    public function testRegisterUpdateProofUsesNewUrl(): void
    {
        $shop = new MockShop('123', 'https://my-shop.com', '1234567890');

        $shopRepository = new MockShopRepository();
        $shopRepository->createShop($shop);

        $registrationService = new RegistrationService(
            $this->appConfiguration,
            $shopRepository,
            new DualSignatureRequestVerifier($this->createMock(RequestVerifier::class)),
            new ResponseSigner(),
            new RandomStringShopSecretGenerator(),
            new NullLogger(),
            new EventDispatcher(),
        );

        $response = $registrationService->register(
            new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-new-shop.com&timestamp=1234567890')
        );

        static::assertSame(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true);

        static::assertIsArray($json);
        static::assertSame('7e5fd4777a328ae756c47e5cd905d587b7c141812fb7d8dd5338b2b1f702adfd', $json['proof'] ?? null);
    }
}

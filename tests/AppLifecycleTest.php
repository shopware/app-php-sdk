<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests;

use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shopware\App\SDK\AppLifecycle;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Authentication\RequestVerifier;
use Shopware\App\SDK\Event\AbstractAppLifecycleEvent;
use Shopware\App\SDK\Event\BeforeShopActivateEvent;
use Shopware\App\SDK\Event\BeforeShopDeactivatedEvent;
use Shopware\App\SDK\Event\BeforeShopDeletionEvent;
use Shopware\App\SDK\Event\ShopActivatedEvent;
use Shopware\App\SDK\Event\ShopDeactivatedEvent;
use Shopware\App\SDK\Event\ShopDeletedEvent;
use Shopware\App\SDK\Exception\ShopNotFoundException;
use Shopware\App\SDK\Registration\RegistrationService;
use Shopware\App\SDK\Shop\ShopResolver;
use Shopware\App\SDK\Test\MockShop;
use Shopware\App\SDK\Test\MockShopRepository;

#[CoversClass(AppLifecycle::class)]
#[CoversClass(ShopNotFoundException::class)]
#[CoversClass(AbstractAppLifecycleEvent::class)]
#[CoversClass(BeforeShopDeletionEvent::class)]
#[CoversClass(ShopDeletedEvent::class)]
#[CoversClass(BeforeShopActivateEvent::class)]
#[CoversClass(BeforeShopDeactivatedEvent::class)]
#[CoversClass(ShopDeactivatedEvent::class)]
#[CoversClass(ShopActivatedEvent::class)]
#[CoversClass(ShopResolver::class)]
#[CoversClass(MockShop::class)]
#[CoversClass(MockShopRepository::class)]
class AppLifecycleTest extends TestCase
{
    private MockShopRepository $shopRepository;
    private AppLifecycle $appLifecycle;

    /**
     * @var array<object>
     */
    private array $events = [];

    protected function setUp(): void
    {
        $this->shopRepository = new MockShopRepository();

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->method('dispatch')
            ->willReturnCallback(function ($event) {
                $this->events[] = $event;
            });

        $this->appLifecycle = new AppLifecycle(
            $this->createMock(RegistrationService::class),
            new ShopResolver($this->shopRepository, $this->createMock(RequestVerifier::class)),
            $this->shopRepository,
            new NullLogger(),
            $eventDispatcher
        );
    }

    public function testRegister(): void
    {
        $register = $this->createMock(RegistrationService::class);
        $register
            ->expects(static::once())
            ->method('register')
            ->willReturn(new Response(204));

        $appLifecycle = new AppLifecycle(
            $register,
            $this->createMock(ShopResolver::class),
            $this->createMock(MockShopRepository::class),
        );

        $response = $appLifecycle->register(new Request("POST", '/?shop-id=123', [], '{}'));
        static::assertSame(204, $response->getStatusCode());
    }

    public function testRegisterConfirm(): void
    {
        $register = $this->createMock(RegistrationService::class);
        $register
            ->expects(static::once())
            ->method('registerConfirm')
            ->willReturn(new Response(204));

        $appLifecycle = new AppLifecycle(
            $register,
            $this->createMock(ShopResolver::class),
            $this->createMock(MockShopRepository::class),
        );

        $response = $appLifecycle->registerConfirm(new Request("POST", '/?shop-id=123', [], '{}'));
        static::assertSame(204, $response->getStatusCode());
    }

    public function testUninstall(): void
    {
        $this->shopRepository->createShop(new MockShop('123', 'https://foo.com', '1234567890'));

        $response = $this->appLifecycle->uninstall(new Request("POST", '/?shop-id=123', [], '{}'));
        static::assertSame(204, $response->getStatusCode());

        static::assertNull($this->shopRepository->getShopFromId('123'));

        static::assertCount(2, $this->events);
        static::assertArrayHasKey('0', $this->events);
        static::assertArrayHasKey('1', $this->events);
        static::assertInstanceOf(BeforeShopDeletionEvent::class, $this->events[0]);
        static::assertInstanceOf(ShopDeletedEvent::class, $this->events[1]);
    }

    public function testUninstallNotExisting(): void
    {
        $response = $this->appLifecycle->uninstall(new Request("POST", '/?shop-id=123', [], '{}'));
        static::assertSame(204, $response->getStatusCode());

        static::assertNull($this->shopRepository->getShopFromId('123'));

        static::assertCount(0, $this->events);
    }

    public function testUninstallWithoutEventDispatcher(): void
    {
        $appLifeCycle = new AppLifecycle(
            $this->createMock(RegistrationService::class),
            new ShopResolver($this->shopRepository, $this->createMock(RequestVerifier::class)),
            $this->shopRepository,
            new NullLogger(),
            null
        );

        $this->shopRepository->createShop(new MockShop('123', 'https://foo.com', '1234567890'));
        $response = $appLifeCycle->uninstall(new Request("POST", '/?shop-id=123', [], '{}'));
        static::assertSame(204, $response->getStatusCode());

        static::assertNull($this->shopRepository->getShopFromId('123'));
    }

    public function testUninstallLogs(): void
    {
        $this->shopRepository->createShop(new MockShop('123', 'https://foo.com', '1234567890'));

        $logger = static::createMock(LoggerInterface::class);
        $logger
            ->expects(static::once())
            ->method('info')
            ->with('Shop uninstalled', ['shop-id' => '123', 'shop-url' => 'https://foo.com']);

        $appLifeCycle = new AppLifecycle(
            $this->createMock(RegistrationService::class),
            new ShopResolver($this->shopRepository, $this->createMock(RequestVerifier::class)),
            $this->shopRepository,
            $logger,
            null
        );

        $response = $appLifeCycle->uninstall(new Request("POST", '/?shop-id=123', [], '{}'));
        static::assertSame(204, $response->getStatusCode());
    }

    public function testActivate(): void
    {
        $this->shopRepository->createShop(new MockShop('123', 'https://foo.com', '1234567890'));

        $response = $this->appLifecycle->activate(new Request("POST", '/?shop-id=123', [], '{}'));
        static::assertSame(204, $response->getStatusCode());

        $shop = $this->shopRepository->getShopFromId('123');
        static::assertNotNull($shop);

        static::assertTrue($shop->isShopActive());

        static::assertCount(2, $this->events);
        static::assertArrayHasKey('0', $this->events);
        static::assertArrayHasKey('1', $this->events);
        static::assertInstanceOf(BeforeShopActivateEvent::class, $this->events[0]);
        static::assertInstanceOf(ShopActivatedEvent::class, $this->events[1]);
    }

    public function testDeactivate(): void
    {
        $this->shopRepository->createShop(new MockShop('123', 'https://foo.com', '1234567890'));

        $response = $this->appLifecycle->deactivate(new Request("POST", '/?shop-id=123', [], '{}'));
        static::assertSame(204, $response->getStatusCode());

        $shop = $this->shopRepository->getShopFromId('123');
        static::assertNotNull($shop);

        static::assertFalse($shop->isShopActive());

        static::assertCount(2, $this->events);
        static::assertArrayHasKey('0', $this->events);
        static::assertArrayHasKey('1', $this->events);
        static::assertInstanceOf(BeforeShopDeactivatedEvent::class, $this->events[0]);
        static::assertInstanceOf(ShopDeactivatedEvent::class, $this->events[1]);
    }

    public function testDeactivateNotFound(): void
    {
        $response = $this->appLifecycle->deactivate(new Request("POST", '/?shop-id=123', [], '{}'));
        static::assertSame(204, $response->getStatusCode());

        $shop = $this->shopRepository->getShopFromId('123');
        static::assertNull($shop);

        static::assertCount(0, $this->events);
    }

    public function testHandleShopStatusWithoutEventDispatcher(): void
    {
        $appLifeCycle = new AppLifecycle(
            $this->createMock(RegistrationService::class),
            new ShopResolver($this->shopRepository, $this->createMock(RequestVerifier::class)),
            $this->shopRepository,
            new NullLogger(),
            null
        );

        $this->shopRepository->createShop(new MockShop('123', 'https://foo.com', '1234567890'));
        $response = $appLifeCycle->deactivate(new Request("POST", '/?shop-id=123', [], '{}'), false);
        static::assertSame(204, $response->getStatusCode());

        $shop = $this->shopRepository->getShopFromId('123');

        static::assertNotNull($shop);
        static::assertFalse($shop->isShopActive());

        $response = $appLifeCycle->activate(new Request("POST", '/?shop-id=123', [], '{}'), true);
        static::assertSame(204, $response->getStatusCode());

        $shop = $this->shopRepository->getShopFromId('123');

        static::assertNotNull($shop);
        static::assertTrue($shop->isShopActive());
    }

    public function testHandleShopStatusLogsActivated(): void
    {
        $this->shopRepository->createShop(new MockShop('123', 'https://foo.com', '1234567890'));

        $logger = static::createMock(LoggerInterface::class);
        $logger
            ->expects(static::once())
            ->method('info')
            ->with('Shop activated', ['shop-id' => '123', 'shop-url' => 'https://foo.com']);

        $appLifeCycle = new AppLifecycle(
            $this->createMock(RegistrationService::class),
            new ShopResolver($this->shopRepository, $this->createMock(RequestVerifier::class)),
            $this->shopRepository,
            $logger,
            null
        );

        $response = $appLifeCycle->activate(new Request("POST", '/?shop-id=123', [], '{}'), true);
        static::assertSame(204, $response->getStatusCode());

        $shop = $this->shopRepository->getShopFromId('123');

        static::assertNotNull($shop);
        static::assertTrue($shop->isShopActive());
    }

    public function testHandleShopStatusLogsDeactivated(): void
    {
        $this->shopRepository->createShop(new MockShop('123', 'https://foo.com', '1234567890'));

        $logger = static::createMock(LoggerInterface::class);
        $logger
            ->expects(static::once())
            ->method('info')
            ->with('Shop deactivated', ['shop-id' => '123', 'shop-url' => 'https://foo.com']);

        $appLifeCycle = new AppLifecycle(
            $this->createMock(RegistrationService::class),
            new ShopResolver($this->shopRepository, $this->createMock(RequestVerifier::class)),
            $this->shopRepository,
            $logger,
            null
        );

        $response = $appLifeCycle->deactivate(new Request("POST", '/?shop-id=123', [], '{}'), false);
        static::assertSame(204, $response->getStatusCode());

        $shop = $this->shopRepository->getShopFromId('123');

        static::assertNotNull($shop);
        static::assertFalse($shop->isShopActive());
    }
}

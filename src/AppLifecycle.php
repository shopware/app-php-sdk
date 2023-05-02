<?php

declare(strict_types=1);

namespace Shopware\App\SDK;

use Http\Discovery\Psr17Factory;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shopware\App\SDK\Event\BeforeShopActivateEvent;
use Shopware\App\SDK\Event\BeforeShopDeactivatedEvent;
use Shopware\App\SDK\Event\BeforeShopDeletionEvent;
use Shopware\App\SDK\Event\ShopActivatedEvent;
use Shopware\App\SDK\Event\ShopDeactivatedEvent;
use Shopware\App\SDK\Event\ShopDeletedEvent;
use Shopware\App\SDK\Exception\ShopNotFoundException;
use Shopware\App\SDK\Registration\RegistrationService;
use Shopware\App\SDK\Shop\ShopInterface;
use Shopware\App\SDK\Shop\ShopRepositoryInterface;
use Shopware\App\SDK\Shop\ShopResolver;

/**
 * This app lifecycle considers all events that can occur during Shopware
 */
class AppLifecycle
{
    public function __construct(
        private readonly RegistrationService $registrationService,
        private readonly ShopResolver $shopResolver,
        private readonly ShopRepositoryInterface $shopRepository,
        private readonly LoggerInterface $logger = new NullLogger(),
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
    ) {
    }

    public function register(RequestInterface $request): ResponseInterface
    {
        return $this->registrationService->register($request);
    }

    public function registerConfirm(RequestInterface $request): ResponseInterface
    {
        return $this->registrationService->registerConfirm($request);
    }

    public function activate(RequestInterface $request): ResponseInterface
    {
        return $this->handleShopStatus($request, true);
    }

    public function deactivate(RequestInterface $request): ResponseInterface
    {
        return $this->handleShopStatus($request, false);
    }

    /**
     * Handles the app.uninstalled Hook to remove the shop from the repository
     */
    public function uninstall(RequestInterface $request): ResponseInterface
    {
        $psrFactory = new Psr17Factory();
        $response = $psrFactory->createResponse(204);

        $shop = $this->findShop($request);

        if ($shop === null) {
            return $response;
        }

        $this->eventDispatcher?->dispatch(new BeforeShopDeletionEvent($request, $shop));

        $this->shopRepository->deleteShop($shop->getShopId());

        $this->eventDispatcher?->dispatch(new ShopDeletedEvent($request, $shop));

        $this->logger->info('Shop uninstalled', [
            'shop-id' => $shop->getShopId(),
            'shop-url' => $shop->getShopUrl(),
        ]);

        return $response;
    }

    private function findShop(RequestInterface $request): ?ShopInterface
    {
        try {
            return $this->shopResolver->resolveShop($request);
        } catch (ShopNotFoundException) {
            return null;
        }
    }

    public function handleShopStatus(RequestInterface $request, bool $status): ResponseInterface
    {
        $psrFactory = new Psr17Factory();
        $response = $psrFactory->createResponse(204);

        $shop = $this->findShop($request);

        if ($shop === null) {
            return $response;
        }

        if ($status) {
            $this->eventDispatcher?->dispatch(new BeforeShopActivateEvent($request, $shop));
        } else {
            $this->eventDispatcher?->dispatch(new BeforeShopDeactivatedEvent($request, $shop));
        }

        $this->shopRepository->updateShop($shop->withShopActive($status));

        if ($status) {
            $this->eventDispatcher?->dispatch(new ShopActivatedEvent($request, $shop));
        } else {
            $this->eventDispatcher?->dispatch(new ShopDeactivatedEvent($request, $shop));
        }

        $this->logger->info($status ? 'Shop activated' : 'Shop deactivated', [
            'shop-id' => $shop->getShopId(),
            'shop-url' => $shop->getShopUrl(),
        ]);

        return $response;
    }
}

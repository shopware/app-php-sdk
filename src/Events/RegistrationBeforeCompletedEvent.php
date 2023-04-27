<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Events;

use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\Shop\ShopInterface;

/**
 * This event is fired before the shop has been saved to the database. So you are able to modify the shop before it is saved.
 */
class RegistrationBeforeCompletedEvent
{
    /**
     * @param ShopInterface $shop
     * @param RequestInterface $request
     * @param array{apiKey: string, secretKey: string} $confirmation
     */
    public function __construct(private readonly ShopInterface $shop, private readonly RequestInterface $request, private readonly array $confirmation)
    {
    }

    public function getShop(): ShopInterface
    {
        return $this->shop;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return array{apiKey: string, secretKey: string}
     */
    public function getConfirmation(): array
    {
        return $this->confirmation;
    }
}

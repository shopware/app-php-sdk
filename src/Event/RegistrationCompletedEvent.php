<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Event;

use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\Shop\ShopInterface;

/**
 * This event is fired when a shop has been finished with the registration. (Already persisted in the database)
 */
class RegistrationCompletedEvent
{
    public function __construct(private readonly ShopInterface $shop, private readonly RequestInterface $request)
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
}

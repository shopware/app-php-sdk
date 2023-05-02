<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Event;

use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\Shop\ShopInterface;

abstract class AbstractAppLifecycleEvent
{
    public function __construct(private readonly RequestInterface $request, private readonly ShopInterface $shop)
    {
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getShop(): ShopInterface
    {
        return $this->shop;
    }
}

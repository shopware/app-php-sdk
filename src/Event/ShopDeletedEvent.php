<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Event;

use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\Shop\ShopInterface;

class ShopDeletedEvent extends AbstractAppLifecycleEvent
{
    public function __construct(
        RequestInterface $request,
        ShopInterface $shop,
        private readonly bool $keepUserData = false,
    ) {
        parent::__construct($request, $shop);
    }

    /**
     * Whether the merchant chose to keep the shop's data when uninstalling the app.
     * When true, the shop was left in the repository instead of being deleted.
     */
    public function keepUserData(): bool
    {
        return $this->keepUserData;
    }
}

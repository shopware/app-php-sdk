<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Event;

use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\Shop\ShopInterface;

class BeforeShopDeletionEvent extends AbstractAppLifecycleEvent
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
     * When true, the SDK leaves the shop in the repository instead of deleting it.
     */
    public function keepUserData(): bool
    {
        return $this->keepUserData;
    }
}

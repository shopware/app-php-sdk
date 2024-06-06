<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Storefront;

use Shopware\App\SDK\Context\InAppPurchase\InAppPurchase;
use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Shop\ShopInterface;

class StorefrontAction
{
    /**
     * @param Collection<InAppPurchase> $inAppPurchases
     */
    public function __construct(
        public readonly ShopInterface $shop,
        public readonly StorefrontClaims $claims,
        public readonly Collection $inAppPurchases,
    ) {
    }
}

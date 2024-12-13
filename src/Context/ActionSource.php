<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context;

use Shopware\App\SDK\Context\InAppPurchase\InAppPurchase;
use Shopware\App\SDK\Framework\Collection;

class ActionSource
{
    /**
     * @param string $url The shop url
     * @param string $appVersion The installed App version
     * @param Collection<InAppPurchase> $inAppPurchases The active in-app-purchases
     */
    public function __construct(
        public readonly string $url,
        public readonly string $appVersion,
        public readonly Collection $inAppPurchases,
    ) {
    }
}

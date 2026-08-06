<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context;

use Shopware\App\SDK\Context\InAppPurchase\InAppPurchase;
use Shopware\App\SDK\Framework\Collection;

class ActionSource
{
    /**
     * @deprecated 6.0.0 - $shopwareVersion should not be nullable
     *
     * @param string $url The shop url
     * @param string $appVersion The installed App version
     * @param Collection<InAppPurchase> $inAppPurchases The active in-app-purchases
     * @param ?string $shopwareVersion The Shopware version provided by the header sw-version
     * @param ?string $userLanguage The ISO-Code of the language used by the storefront customer or admin user
     */
    public function __construct(
        public readonly string $url,
        public readonly string $appVersion,
        public readonly Collection $inAppPurchases,
        public readonly ?string $shopwareVersion = null,
        public readonly ?string $userLanguage = null,
    ) {
    }
}

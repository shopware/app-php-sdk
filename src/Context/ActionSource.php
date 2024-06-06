<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context;

class ActionSource
{
    /**
     * @param string $url The shop url
     * @param string $appVersion The installed App version
     * @param string[] $inAppPurchases The active in-app-purchases
     */
    public function __construct(
        public readonly string $url,
        public readonly string $appVersion,
        public readonly array $inAppPurchases = [],
    ) {
    }

    public function hasInAppPurchase(string $inAppPurchase): bool
    {
        return \in_array($inAppPurchase, $this->inAppPurchases, true);
    }
}

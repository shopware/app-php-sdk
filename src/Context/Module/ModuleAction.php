<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Module;

use Shopware\App\SDK\Shop\ShopInterface;

class ModuleAction
{
    /**
     * @param string $contentLanguage - The language of the Shopware content as UUID
     * @param string $userLanguage - The language of the Shopware user as ISO (en-GB)
     * @param string[] $inAppPurchases - The active in-app-purchases
     */
    public function __construct(
        public readonly ShopInterface $shop,
        public readonly string $shopwareVersion,
        public readonly string $contentLanguage,
        public readonly string $userLanguage,
        public readonly array $inAppPurchases = [],
    ) {
    }
}

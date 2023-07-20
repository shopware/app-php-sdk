<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Storefront;

use Shopware\App\SDK\Shop\ShopInterface;

class StorefrontAction
{
    public function __construct(
        public readonly ShopInterface $shop,
        public readonly StorefrontClaims $claims
    ) {
    }
}

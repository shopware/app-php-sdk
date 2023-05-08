<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\TaxProvider;

use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Cart\Cart;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelContext;
use Shopware\App\SDK\Shop\ShopInterface;

class TaxProviderAction
{
    public function __construct(
        public readonly ShopInterface       $shop,
        public readonly ActionSource        $source,
        public readonly SalesChannelContext $context,
        public readonly Cart                $cart,
    ) {
    }
}

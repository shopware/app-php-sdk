<?php declare(strict_types=1);

namespace Shopware\App\SDK\Context\Gateway\Context;

use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Cart\Cart;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelContext;
use Shopware\App\SDK\Shop\ShopInterface;

class ContextGatewayAction
{
    public function __construct(
        public readonly ShopInterface $shop,
        public readonly ActionSource $source,
        public readonly Cart $cart,
        public readonly SalesChannelContext $context,
    ) {
    }
}
<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Gateway\Checkout;

use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Cart\Cart;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelContext;
use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Shop\ShopInterface;

class CheckoutGatewayAction
{
    /**
     * @param Collection<string> $paymentMethods
     * @param Collection<string> $shippingMethods
     */
    public function __construct(
        public readonly ShopInterface $shop,
        public readonly ActionSource $source,
        public readonly Cart $cart,
        public readonly SalesChannelContext $context,
        public readonly Collection $paymentMethods,
        public readonly Collection $shippingMethods,
    ) {
    }
}

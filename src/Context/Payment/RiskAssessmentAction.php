<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Payment;

use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Cart\Cart;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelContext;
use Shopware\App\SDK\Shop\ShopInterface;

class RiskAssessmentAction
{
    /**
     * @param array<string, string> $paymentMethods
     * @param array<string, string> $shippingMethods
     */
    public function __construct(
        public readonly ShopInterface $shop,
        public readonly ActionSource $source,
        public readonly Cart $cart,
        public readonly SalesChannelContext $context,
        public readonly array $paymentMethods = [],
        public readonly array $shippingMethods = [],
    ) {
    }
}

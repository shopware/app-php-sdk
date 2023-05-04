<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Payment;

use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Cart\Cart;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelContext;
use Shopware\App\SDK\Shop\ShopInterface;

/**
 * The prepared payment calls first the validation endpoint to verify the payment session given by the client.
 * After the validation, the order will be created and the capture endpoint will be called.
 */
class PaymentValidateAction
{
    /**
     * @param array<mixed> $requestData - Contains all parameters passed to the cart
     */
    public function __construct(
        public readonly ShopInterface $shop,
        public readonly ActionSource $source,
        public readonly Cart $cart,
        public readonly SalesChannelContext $salesChannelContext,
        public readonly array $requestData
    ) {
    }
}

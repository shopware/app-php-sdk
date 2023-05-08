<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Payment;

use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Order\Order;
use Shopware\App\SDK\Shop\ShopInterface;

/**
 * This action refers to the payload of <refynd-url> in the app manifest
 */
class RefundAction
{
    public function __construct(
        public readonly ShopInterface $shop,
        public readonly ActionSource $source,
        public readonly Order $order,
        public readonly Refund $refund,
    ) {
    }
}

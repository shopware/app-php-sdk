<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Payment;

use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Order\Order;
use Shopware\App\SDK\Context\Order\OrderTransaction;
use Shopware\App\SDK\Shop\ShopInterface;

class PaymentCaptureAction
{
    /**
     * @param array<mixed> $requestData - Contains the result of PaymentResponse::validateSuccessResponse
     */
    public function __construct(
        public readonly ShopInterface $shop,
        public readonly ActionSource $source,
        public readonly Order $order,
        public readonly OrderTransaction $orderTransaction,
        public readonly array $requestData
    ) {
    }
}

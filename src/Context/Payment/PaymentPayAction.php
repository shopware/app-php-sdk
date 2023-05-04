<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Payment;

use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Order\Order;
use Shopware\App\SDK\Context\Order\OrderTransaction;
use Shopware\App\SDK\Shop\ShopInterface;

/**
 * This action refers to the payload of <pay-url> in the app manifest
 */
class PaymentPayAction
{
    /**
     * @param string|null $returnUrl - Return url is only provided on async payments
     * @param array<mixed> $requestData - Request data is only provided on async payments
     */
    public function __construct(
        public readonly ShopInterface $shop,
        public readonly ActionSource $source,
        public readonly Order $order,
        public readonly OrderTransaction $orderTransaction,
        public readonly ?string $returnUrl,
        public readonly array $requestData
    ) {
    }
}

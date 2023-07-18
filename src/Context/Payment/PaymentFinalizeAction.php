<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Payment;

use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Order\OrderTransaction;
use Shopware\App\SDK\Shop\ShopInterface;

/**
 * This action is called
 */
class PaymentFinalizeAction
{
    /**
     * @param array<mixed> $queryParameters - Contains all query parameters passed to Shopware at the redirect of the payment provider
     */
    public function __construct(
        public readonly ShopInterface $shop,
        public readonly ActionSource $source,
        public readonly OrderTransaction $orderTransaction,
        public readonly ?RecurringData $recurring = null,
        public readonly array $queryParameters = [],
    ) {
    }
}

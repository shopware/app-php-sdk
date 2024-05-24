<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Gateway\Checkout\Command;

use Shopware\App\SDK\Gateway\Checkout\CheckoutGatewayCommand;

/**
 * This command is used to remove an available payment method from the checkout process.
 * Customers will no longer be able to select the removed payment method during the checkout process and any checkout with this payment method will be blocked.
 * A `PaymentMethodBlockedError` will be added automatically in that case.
 */
class RemovePaymentMethodCommand extends CheckoutGatewayCommand
{
    final public const KEY = 'remove-payment-method';

    /**
     * @param string $paymentMethodTechnicalName - The technical name of the payment method to be removed
     */
    public function __construct(public readonly string $paymentMethodTechnicalName)
    {
        $this->keyName = self::KEY;
        $this->setPayloadValue('paymentMethodTechnicalName', $paymentMethodTechnicalName);
    }
}

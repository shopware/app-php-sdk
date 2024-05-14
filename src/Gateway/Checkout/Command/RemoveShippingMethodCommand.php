<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Gateway\Checkout\Command;

use Shopware\App\SDK\Gateway\Checkout\CheckoutGatewayCommand;

/**
 * This command is used to remove an available shipping method from the checkout process.
 * Customers will no longer be able to select the removed shipping method during the checkout process and any checkout with this shipping method will be blocked.
 * A `ShippingMethodBlockedError` will be added automatically in that case.
 */
class RemoveShippingMethodCommand extends CheckoutGatewayCommand
{
    final public const KEY = 'remove-shipping-method';

    /**
     * @param string $shippingMethodTechnicalName - The technical name of the shipping method to be removed
     */
    public function __construct(public readonly string $shippingMethodTechnicalName)
    {
        $this->keyName = self::KEY;
        $this->setPayloadValue('shippingMethodTechnicalName', $shippingMethodTechnicalName);
    }
}

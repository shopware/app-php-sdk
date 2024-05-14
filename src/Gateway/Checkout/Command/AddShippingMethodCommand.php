<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Gateway\Checkout\Command;

use Shopware\App\SDK\Gateway\Checkout\CheckoutGatewayCommand;

/**
 * @experimental - do not use yet, selection of newly added checkout methods does not work yet
 */
class AddShippingMethodCommand extends CheckoutGatewayCommand
{
    final public const KEY = 'add-shipping-method';

    public function __construct(public readonly string $shippingMethodTechnicalName)
    {
        $this->keyName = self::KEY;
        $this->setPayloadValue('shippingMethodTechnicalName', $shippingMethodTechnicalName);
    }
}

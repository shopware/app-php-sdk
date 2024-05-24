<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Gateway\Checkout\Command;

use Shopware\App\SDK\Gateway\Checkout\CheckoutGatewayCommand;

/**
 * This command is used to add an extension to a payment method during the checkout process.
 * The extension will be stored in the `_extensions` property of the payment method.
 */
class AddPaymentMethodExtensionCommand extends CheckoutGatewayCommand
{
    final public const KEY = 'add-payment-method-extension';

    /**
     * @param string $paymentMethodTechnicalName - The technical name of the payment method
     * @param string $extensionKey - The array-key of the newly to be added extension
     * @param array<array-key, mixed> $extensionsPayload - The payload of the newly to be added extension
     */
    public function __construct(
        public readonly string $paymentMethodTechnicalName,
        public readonly string $extensionKey,
        public readonly array $extensionsPayload,
    ) {
        $this->keyName = self::KEY;
        $this->setPayloadValue('paymentMethodTechnicalName', $paymentMethodTechnicalName);
        $this->setPayloadValue('extensionKey', $extensionKey);
        $this->setPayloadValue('extensionsPayload', $extensionsPayload);
    }
}

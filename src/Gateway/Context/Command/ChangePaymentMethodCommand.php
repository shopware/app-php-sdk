<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Gateway\Context\Command;

use Shopware\App\SDK\Gateway\Context\ContextGatewayCommand;

/**
 * Changes the active payment method in the context.
 * Beware that the payment method should be valid for the current context.
 */
class ChangePaymentMethodCommand extends ContextGatewayCommand
{
    final public const KEY = 'context_change-payment-method';

    /**
     * @param string $technicalName - The technical name of the payment method to be set.
     */
    public function __construct(
        public string $technicalName,
    ) {
        $this->keyName = self::KEY;
        $this->setPayloadValue('technicalName', $technicalName);
    }
}

<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Gateway\Context\Command;

use Shopware\App\SDK\Gateway\Context\ContextGatewayCommand;

/**
 * Changes the active shipping method in the context.
 * Beware that the shipping method should be valid for the current context.
 */
class ChangeShippingMethodCommand extends ContextGatewayCommand
{
    final public const KEY = 'context_change-shipping-method';

    /**
     * @param string $technicalName - The technical name of the shipping method to be set.
     */
    public function __construct(
        public string $technicalName,
    ) {
        $this->keyName = self::KEY;
        $this->setPayloadValue('technicalName', $technicalName);
    }
}

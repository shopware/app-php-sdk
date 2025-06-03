<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Gateway\Context\Command;

use Shopware\App\SDK\Gateway\Context\ContextGatewayCommand;

/**
 * Changes the active currency for logged in or newly registered customers
 * Be aware that the currency must be available in the sales channel
 */
class ChangeCurrencyCommand extends ContextGatewayCommand
{
    final public const KEY = 'context_change-currency';

    /**
     * @param string $iso - The ISO 4217 currency code, e.g. EUR, USD, GBP
     */
    public function __construct(
        public string $iso,
    ) {
        $this->keyName = self::KEY;
        $this->setPayloadValue('iso', $iso);
    }
}

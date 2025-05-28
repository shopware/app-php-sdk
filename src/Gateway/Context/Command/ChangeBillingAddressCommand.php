<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Gateway\Context\Command;

use Shopware\App\SDK\Gateway\Context\ContextGatewayCommand;

/**
 * Changes the billing address of the current customer context to the specified address ID.
 * Be aware, that the address must be available for the current customer.
 */
class ChangeBillingAddressCommand extends ContextGatewayCommand
{
    final public const KEY = 'context_change-billing-address';

    /**
     * @param string $addressId - The address id to set as active billing address.
     */
    public function __construct(
        public string $addressId,
    ) {
        $this->keyName = self::KEY;
        $this->setPayloadValue('addressId', $addressId);
    }
}

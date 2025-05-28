<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Gateway\Context\Command;

use Shopware\App\SDK\Gateway\Context\ContextGatewayCommand;

/**
 * Adds an error message to be displayed to the customer in the Storefront via FlashBag messages.
 */
class AddCustomerMessageCommand extends ContextGatewayCommand
{
    final public const KEY = 'context_add-customer-message';

    /**
     * @param string $message - The message to be displayed to the customer.
     */
    public function __construct(
        public string $message,
    ) {
        $this->keyName = self::KEY;
        $this->setPayloadValue('message', $message);
    }
}

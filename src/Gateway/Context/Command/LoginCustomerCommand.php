<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Gateway\Context\Command;

use Shopware\App\SDK\Gateway\Context\ContextGatewayCommand;

/**
 * Logs in a customer by their email address.
 */
class LoginCustomerCommand extends ContextGatewayCommand
{
    final public const KEY = 'context_login-customer';

    /**
     * @param string $customerEmail - The email address of the customer to log in.
     */
    public function __construct(
        public string $customerEmail,
    ) {
        $this->keyName = self::KEY;
        $this->setPayloadValue('customerEmail', $customerEmail);
    }
}

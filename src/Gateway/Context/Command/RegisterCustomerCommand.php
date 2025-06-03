<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Gateway\Context\Command;

use Shopware\App\SDK\Context\Response\Customer\CustomerResponseStruct;
use Shopware\App\SDK\Gateway\Context\ContextGatewayCommand;

/**
 * Registers a new customer and logs them in.
 */
class RegisterCustomerCommand extends ContextGatewayCommand
{
    final public const KEY = 'context_register-customer';

    /**
     * @param CustomerResponseStruct $data - The customer data to register.
     */
    public function __construct(
        public CustomerResponseStruct $data,
    ) {
        $this->keyName = self::KEY;
        $this->setPayloadValue('data', $data);
    }
}

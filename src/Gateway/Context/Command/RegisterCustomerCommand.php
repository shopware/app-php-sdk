<?php declare(strict_types=1);

namespace Shopware\App\SDK\Gateway\Context\Command;

use Shopware\App\SDK\Context\Response\Customer\CustomerResponseStruct;
use Shopware\App\SDK\Gateway\Context\ContextGatewayCommand;

class RegisterCustomerCommand extends ContextGatewayCommand
{
    final public const KEY = 'context_register-customer';

    public function __construct(
        public CustomerResponseStruct $data,
    ) {
        $this->keyName = self::KEY;
        $this->setPayloadValue('data', $data);
    }
}
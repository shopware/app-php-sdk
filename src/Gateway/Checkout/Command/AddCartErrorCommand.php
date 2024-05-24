<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Gateway\Checkout\Command;

use Shopware\App\SDK\Context\Cart\Error;
use Shopware\App\SDK\Gateway\Checkout\CheckoutGatewayCommand;

/**
 * This command is used to add an error to the cart during the checkout process.
 * It can be used to display a message to the customer in form of a flash message during checkout.
 * The error can be blocking, which means that the checkout process will be stopped and the customer will be informed about the error.
 */
class AddCartErrorCommand extends CheckoutGatewayCommand
{
    final public const KEY = 'add-cart-error';

    /**
     * @param string $message - The message to be displayed to the customer during the checkout
     * @param bool $blocking - If the error should block the checkout process
     * @param int $level - Controls the severity of the error, especially useful to control the type of flash message displayed to the customer (error, warning, notice)
     */
    public function __construct(
        public readonly string $message,
        public readonly bool $blocking = false,
        public readonly int $level = Error::LEVEL_WARNING,
    ) {
        $this->keyName = self::KEY;
        $this->setPayloadValue('message', $message);
        $this->setPayloadValue('blocking', $blocking);
        $this->setPayloadValue('level', $level);
    }
}

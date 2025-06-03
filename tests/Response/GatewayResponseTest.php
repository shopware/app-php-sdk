<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Response;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Gateway\Checkout\Command\AddPaymentMethodCommand;
use Shopware\App\SDK\Gateway\Checkout\Command\RemovePaymentMethodCommand;
use Shopware\App\SDK\Gateway\Context\Command\ChangePaymentMethodCommand;
use Shopware\App\SDK\Gateway\Context\Command\LoginCustomerCommand;
use Shopware\App\SDK\Response\GatewayResponse;

#[CoversClass(GatewayResponse::class)]
class GatewayResponseTest extends TestCase
{
    public function testCreateCheckoutGatewayResponse(): void
    {
        $command1 = new AddPaymentMethodCommand('paypal');
        $command2 = new RemovePaymentMethodCommand('credit_card');

        $commands = new Collection([$command1, $command2]);

        $response = GatewayResponse::createCheckoutGatewayResponse($commands);

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('[{"command":"add-payment-method","payload":{"paymentMethodTechnicalName":"paypal"}},{"command":"remove-payment-method","payload":{"paymentMethodTechnicalName":"credit_card"}}]', $response->getBody()->getContents());
    }

    public function testCreateContextGatewayResponse(): void
    {
        $command1 = new LoginCustomerCommand('foo@bar.com');
        $command2 = new ChangePaymentMethodCommand('credit_card');

        $commands = new Collection([$command1, $command2]);

        $response = GatewayResponse::createContextGatewayResponse($commands);

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('[{"command":"context_login-customer","payload":{"customerEmail":"foo@bar.com"}},{"command":"context_change-payment-method","payload":{"technicalName":"credit_card"}}]', $response->getBody()->getContents());
    }
}

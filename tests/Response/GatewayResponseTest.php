<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Response;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Gateway\Checkout\Command\AddPaymentMethodCommand;
use Shopware\App\SDK\Gateway\Checkout\Command\RemovePaymentMethodCommand;
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
}

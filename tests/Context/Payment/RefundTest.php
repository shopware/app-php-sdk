<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Payment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\CalculatedPrice;
use Shopware\App\SDK\Context\Order\StateMachineState;
use Shopware\App\SDK\Context\Payment\Refund;
use Shopware\App\SDK\Context\Payment\RefundTransactionCapture;

#[CoversClass(Refund::class)]
class RefundTest extends TestCase
{
    public function testConstruct(): void
    {
        $price = [
            'unitPrice' => 1.0,
            'totalPrice' => 2.0,
            'quantity' => 3,
            'calculatedTaxes' => [],
            'taxRules' => [],
        ];

        $stateMachineState = ['id' => 'foo', 'technicalName' => 'bar'];

        $transactionCapture = ['externalReference' => 'foo'];

        $refund = new Refund([
            'id' => 'foo',
            'reason' => 'reason',
            'amount' => $price,
            'stateMachineState' => $stateMachineState,
            'transactionCapture' => $transactionCapture,
        ]);

        static::assertSame('foo', $refund->getId());
        static::assertSame('reason', $refund->getReason());
        static::assertEquals(new CalculatedPrice($price), $refund->getAmount());
        static::assertEquals(new StateMachineState($stateMachineState), $refund->getStateMachineState());
        static::assertEquals(new RefundTransactionCapture($transactionCapture), $refund->getTransactionCapture());
    }

    public function testConstructWithNullables(): void
    {
        $refund = new Refund([
            'reason' => null,
        ]);

        static::assertNull($refund->getReason());
    }
}

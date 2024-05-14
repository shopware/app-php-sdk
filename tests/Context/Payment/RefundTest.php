<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Payment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Payment\Refund;

#[CoversClass(Refund::class)]
class RefundTest extends TestCase
{
    public function testConstruct(): void
    {
        $data = [
            'id' => 'refund-id',
            'reason' => 'reason',
            'amount' => [
                'totalPrice' => 100,
            ],
            'stateMachineState' => [
                'technicalName' => 'open',
            ],
            'transactionCapture' => [
                'externalReference' => 'reference-id',
            ],
        ];

        $refund = new Refund($data);

        static::assertSame('refund-id', $refund->getId());
        static::assertSame('reason', $refund->getReason());
        static::assertSame(100.0, $refund->getAmount()->getTotalPrice());
        static::assertSame('open', $refund->getStateMachineState()->getTechnicalName());
        static::assertSame('reference-id', $refund->getTransactionCapture()->getExternalReference());
    }

    public function testConstructWithNullReason(): void
    {
        $data = [
            'id' => 'refund-id',
            'reason' => null,
            'amount' => [
                'totalPrice' => 100,
            ],
            'stateMachineState' => [
                'technicalName' => 'open',
            ],
            'transactionCapture' => [
                'externalReference' => 'reference-id',
            ],
        ];

        $refund = new Refund($data);

        static::assertNull($refund->getReason());
        static::assertSame(100.0, $refund->getAmount()->getTotalPrice());
        static::assertSame('open', $refund->getStateMachineState()->getTechnicalName());
        static::assertSame('reference-id', $refund->getTransactionCapture()->getExternalReference());
    }
}

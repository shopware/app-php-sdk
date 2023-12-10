<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Payment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\CalculatedPrice;
use Shopware\App\SDK\Context\Order\OrderTransaction;
use Shopware\App\SDK\Context\Payment\RefundTransactionCapture;

#[CoversClass(RefundTransactionCapture::class)]
class RefundTransactionCaptureTest extends TestCase
{
    public function testConstruct(): void
    {
        $amount = [
            'unitPrice' => 1.0,
            'totalPrice' => 2.0,
            'quantity' => 3,
            'calculatedTaxes' => [],
            'taxRules' => [],
        ];

        $transaction = ['id' => 'foo'];

        $refundTransactionCapture = new RefundTransactionCapture([
            'externalReference' => 'external-reference',
            'amount' => $amount,
            'transaction' => $transaction,
        ]);

        static::assertSame('external-reference', $refundTransactionCapture->getExternalReference());
        static::assertEquals(new CalculatedPrice($amount), $refundTransactionCapture->getAmount());
        static::assertEquals(new OrderTransaction($transaction), $refundTransactionCapture->getTransaction());
    }

    public function testConstructWithNullables(): void
    {
        $refundTransactionCapture = new RefundTransactionCapture([
            'externalReference' => null,
        ]);

        static::assertNull($refundTransactionCapture->getExternalReference());
    }
}

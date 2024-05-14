<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Payment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Payment\RefundTransactionCapture;

#[CoversClass(RefundTransactionCapture::class)]
class RefundTransactionCaptureTest extends TestCase
{
    public function testConstruct(): void
    {
        $data = [
            'externalReference' => 'reference-id',
            'amount' => [
                'totalPrice' => 100,
            ],
            'transaction' => [
                'id' => 'transaction-id',
            ],
            'customFields' => [
                'custom-field' => 'custom-value',
            ],
        ];

        $refundTransactionCapture = new RefundTransactionCapture($data);

        static::assertSame('reference-id', $refundTransactionCapture->getExternalReference());
        static::assertSame(100.0, $refundTransactionCapture->getAmount()->getTotalPrice());
        static::assertSame('transaction-id', $refundTransactionCapture->getTransaction()->getId());
        static::assertArrayHasKey('custom-field', $refundTransactionCapture->getCustomFields());
        static::assertSame('custom-value', $refundTransactionCapture->getCustomFields()['custom-field']);
    }

    public function testConstructWithNullExternalReference(): void
    {
        $data = [
            'externalReference' => null,
            'amount' => [
                'totalPrice' => 100,
            ],
            'transaction' => [
                'id' => 'transaction-id',
            ],
            'customFields' => [
                'custom-field' => 'custom-value',
            ],
        ];

        $refundTransactionCapture = new RefundTransactionCapture($data);

        static::assertNull($refundTransactionCapture->getExternalReference());
        static::assertSame(100.0, $refundTransactionCapture->getAmount()->getTotalPrice());
        static::assertSame('transaction-id', $refundTransactionCapture->getTransaction()->getId());
        static::assertArrayHasKey('custom-field', $refundTransactionCapture->getCustomFields());
        static::assertSame('custom-value', $refundTransactionCapture->getCustomFields()['custom-field']);
    }

    public function testConstructWithNullCustomFields(): void
    {
        $data = [
            'externalReference' => 'reference-id',
            'amount' => [
                'totalPrice' => 100,
            ],
            'transaction' => [
                'id' => 'transaction-id',
            ],
            'customFields' => null,
        ];

        $refundTransactionCapture = new RefundTransactionCapture($data);

        static::assertSame('reference-id', $refundTransactionCapture->getExternalReference());
        static::assertSame(100.0, $refundTransactionCapture->getAmount()->getTotalPrice());
        static::assertSame('transaction-id', $refundTransactionCapture->getTransaction()->getId());
        static::assertEmpty($refundTransactionCapture->getCustomFields());
    }
}

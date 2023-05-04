<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Payment;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Payment\PaymentResponse;
use PHPUnit\Framework\TestCase;

#[CoversClass(PaymentResponse::class)]
class PaymentResponseTest extends TestCase
{
    public function testPaid(): void
    {
        $response = PaymentResponse::paid();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"status":"paid"}', $response->getBody()->getContents());
    }

    public function testPaidPartially(): void
    {
        $response = PaymentResponse::paidPartially();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"status":"paid_partially"}', $response->getBody()->getContents());
    }

    public function testCancelled(): void
    {
        $response = PaymentResponse::cancelled();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"status":"cancelled"}', $response->getBody()->getContents());
    }

    public function testFailed(): void
    {
        $response = PaymentResponse::failed();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"status":"failed"}', $response->getBody()->getContents());
    }

    public function testAuthorized(): void
    {
        $response = PaymentResponse::authorized();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"status":"authorized"}', $response->getBody()->getContents());
    }

    public function testUnconfirmed(): void
    {
        $response = PaymentResponse::unconfirmed();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"status":"unconfirmed"}', $response->getBody()->getContents());
    }

    public function testInProgress(): void
    {
        $response = PaymentResponse::inProgress();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"status":"in_progress"}', $response->getBody()->getContents());
    }

    public function testRefunded(): void
    {
        $response = PaymentResponse::refunded();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"status":"refunded"}', $response->getBody()->getContents());
    }

    public function testReminded(): void
    {
        $response = PaymentResponse::reminded();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"status":"reminded"}', $response->getBody()->getContents());
    }

    public function testChargeback(): void
    {
        $response = PaymentResponse::chargeback();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"status":"chargeback"}', $response->getBody()->getContents());
    }
}

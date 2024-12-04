<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Response;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Response\PaymentResponse;
use PHPUnit\Framework\TestCase;

#[CoversClass(PaymentResponse::class)]
class PaymentResponseTest extends TestCase
{
    public function testCreateStatusResponse(): void
    {
        $response = PaymentResponse::createStatusResponse('status');

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"status":"status"}', $response->getBody()->getContents());
    }

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
        static::assertSame('{"status":"cancel"}', $response->getBody()->getContents());
    }

    public function testFailed(): void
    {
        $response = PaymentResponse::failed();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"status":"fail"}', $response->getBody()->getContents());
    }

    public function testAuthorize(): void
    {
        $response = PaymentResponse::authorize();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"status":"authorize"}', $response->getBody()->getContents());
    }

    public function testUnconfirmed(): void
    {
        $response = PaymentResponse::unconfirmed();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"status":"process_unconfirmed"}', $response->getBody()->getContents());
    }

    public function testInProgress(): void
    {
        $response = PaymentResponse::inProgress();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"status":"process"}', $response->getBody()->getContents());
    }

    public function testRefunded(): void
    {
        $response = PaymentResponse::refunded();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"status":"refund"}', $response->getBody()->getContents());
    }

    public function testReminded(): void
    {
        $response = PaymentResponse::reminded();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"status":"remind"}', $response->getBody()->getContents());
    }

    public function testChargeback(): void
    {
        $response = PaymentResponse::chargeback();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"status":"chargeback"}', $response->getBody()->getContents());
    }

    public function testReopen(): void
    {
        $response = PaymentResponse::reopen();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"status":"reopen"}', $response->getBody()->getContents());
    }

    public function testValidateSuccess(): void
    {
        $response = PaymentResponse::validateSuccess(['foo' => 'bar']);

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"preOrderPayment":{"foo":"bar"}}', $response->getBody()->getContents());
    }

    public function testValidateError(): void
    {
        $response = PaymentResponse::validationError('error');

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"message":"error"}', $response->getBody()->getContents());
    }

    public function testRedirect(): void
    {
        $response = PaymentResponse::redirect('https://example.com');

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"redirectUrl":"https:\/\/example.com"}', $response->getBody()->getContents());
    }
}

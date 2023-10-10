<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Response;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Response\RiskAssessmentResponse;

#[CoversClass(RiskAssessmentResponse::class)]
class RiskAssessmentResponseTest extends TestCase
{
    public function testCreateResponseFromConstruct(): void
    {
        $assessment = new RiskAssessmentResponse(
            ['payment-handler-1', 'payment-handler-2', 'payment-handler-1'],
            ['shipment-id-1', 'shipment-id-2', 'shipment-id-2'],
        );

        $response = $assessment->createResponse();

        static::assertSame(200, $response->getStatusCode());

        $body = $response->getBody()->getContents();

        static::assertJson($body);

        $body = \json_decode($body, true, flags: \JSON_THROW_ON_ERROR);

        static::assertIsArray($body);

        static::assertArrayHasKey('paymentMethods', $body);
        static::assertArrayHasKey('shippingMethods', $body);
        static::assertArrayHasKey('errors', $body);

        static::assertCount(2, $body['paymentMethods']);
        static::assertCount(2, $body['shippingMethods']);
        static::assertCount(0, $body['errors']);

        static::assertContains('payment-handler-1', $body['paymentMethods']);
        static::assertContains('payment-handler-2', $body['paymentMethods']);

        static::assertContains('shipment-id-1', $body['shippingMethods']);
        static::assertContains('shipment-id-2', $body['shippingMethods']);
    }

    public function testAddBlockedPaymentMethod(): void
    {
        $assessment = new RiskAssessmentResponse();
        $assessment->addBlockedPaymentMethod('payment-handler-1');

        $response = $assessment->createResponse();

        static::assertSame(200, $response->getStatusCode());

        $body = $response->getBody()->getContents();

        static::assertJson($body);

        $body = \json_decode($body, true, flags: \JSON_THROW_ON_ERROR);

        static::assertIsArray($body);

        static::assertArrayHasKey('paymentMethods', $body);
        static::assertArrayHasKey('shippingMethods', $body);
        static::assertArrayHasKey('errors', $body);

        static::assertCount(1, $body['paymentMethods']);
        static::assertCount(0, $body['shippingMethods']);
        static::assertCount(0, $body['errors']);

        static::assertContains('payment-handler-1', $body['paymentMethods']);
    }

    public function testAddBlockedShippingMethod(): void
    {
        $assessment = new RiskAssessmentResponse();
        $assessment->addBlockedShippingMethod('shipment-id-1');

        $response = $assessment->createResponse();

        static::assertSame(200, $response->getStatusCode());

        $body = $response->getBody()->getContents();

        static::assertJson($body);

        $body = \json_decode($body, true, flags: \JSON_THROW_ON_ERROR);

        static::assertIsArray($body);

        static::assertArrayHasKey('paymentMethods', $body);
        static::assertArrayHasKey('shippingMethods', $body);
        static::assertArrayHasKey('errors', $body);

        static::assertCount(0, $body['paymentMethods']);
        static::assertCount(1, $body['shippingMethods']);
        static::assertCount(0, $body['errors']);

        static::assertContains('shipment-id-1', $body['shippingMethods']);
    }

    public function testAddError(): void
    {
        $assessment = new RiskAssessmentResponse();
        $assessment->addRiskAssessmentError('foo', 20, true);
        $assessment->addRiskAssessmentError('bar', 10);

        $response = $assessment->createResponse();

        static::assertSame(200, $response->getStatusCode());

        $body = $response->getBody()->getContents();

        static::assertJson($body);

        $body = \json_decode($body, true, flags: \JSON_THROW_ON_ERROR);

        static::assertIsArray($body);

        static::assertArrayHasKey('paymentMethods', $body);
        static::assertArrayHasKey('shippingMethods', $body);
        static::assertArrayHasKey('errors', $body);

        static::assertCount(0, $body['paymentMethods']);
        static::assertCount(0, $body['shippingMethods']);
        static::assertCount(2, $body['errors']);

        static::assertArrayHasKey(0, $body['errors']);
        static::assertArrayHasKey('reason', $body['errors'][0]);
        static::assertArrayHasKey('level', $body['errors'][0]);
        static::assertArrayHasKey('blockOrder', $body['errors'][0]);

        static::assertArrayHasKey(1, $body['errors']);
        static::assertArrayHasKey('reason', $body['errors'][1]);
        static::assertArrayHasKey('level', $body['errors'][1]);
        static::assertArrayHasKey('blockOrder', $body['errors'][1]);

        static::assertSame('foo', $body['errors'][0]['reason']);
        static::assertSame(20, $body['errors'][0]['level']);
        static::assertSame(true, $body['errors'][0]['blockOrder']);

        static::assertSame('bar', $body['errors'][1]['reason']);
        static::assertSame(10, $body['errors'][1]['level']);
        static::assertSame(false, $body['errors'][1]['blockOrder']);
    }

    public function testCreateEmptyResponse(): void
    {
        $assessment = new RiskAssessmentResponse();
        $assessment->addBlockedPaymentMethod('payment-handler-1');
        $assessment->addBlockedShippingMethod('shipment-id-1');
        $assessment->addRiskAssessmentError('foo', 20, true);

        $response = $assessment->createEmptyResponse();

        static::assertSame(200, $response->getStatusCode());

        $body = $response->getBody()->getContents();

        static::assertJson($body);

        $body = \json_decode($body, true, flags: \JSON_THROW_ON_ERROR);

        static::assertIsArray($body);

        static::assertArrayHasKey('paymentMethods', $body);
        static::assertArrayHasKey('shippingMethods', $body);
        static::assertArrayHasKey('errors', $body);

        static::assertCount(0, $body['paymentMethods']);
        static::assertCount(0, $body['shippingMethods']);
        static::assertCount(0, $body['errors']);
    }
}

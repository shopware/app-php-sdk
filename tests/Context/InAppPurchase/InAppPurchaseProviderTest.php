<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\InAppPurchase;

use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Shopware\App\SDK\Context\InAppPurchase\InAppPurchaseProvider;
use Shopware\App\SDK\Context\InAppPurchase\SBPStoreKeyFetcher;
use Shopware\App\SDK\Test\JWKSHelper;
use Shopware\App\SDK\Test\MockShop;
use Strobotti\JWK\KeySetFactory;

#[CoversClass(InAppPurchaseProvider::class)]
class InAppPurchaseProviderTest extends TestCase
{
    public function testDecodePurchases(): void
    {
        $token = JWKSHelper::encodeIntoToken(
            [],
            [
                'no-identifier' => ['sub' => 'example.com', 'quantity' => 1],
                'swagInAppPurchase1' => ['identifier' => 'swagInAppPurchase1', 'sub' => 'example.com', 'quantity' => 1],
                'swagInAppPurchase2' => ['identifier' => 'swagInAppPurchase2', 'sub' => 'example.com', 'quantity' => 2, 'nextBookingDate' => '2021-01-01 00:00:00']
            ]
        );

        $shop = new MockShop('shop-id', 'https://example.com', 'secret');

        $fetcher = $this->createMock(SBPStoreKeyFetcher::class);
        $fetcher
            ->expects(static::once())
            ->method('getKey')
            ->with(false)
            ->willReturn((new KeySetFactory())->createFromJSON(JWKSHelper::getPublicJWKS()));

        $provider = new InAppPurchaseProvider($fetcher);
        $IAPs = $provider->decodePurchases($token->toString(), $shop);

        static::assertCount(2, $IAPs);

        $IAP1 = $IAPs->get('swagInAppPurchase1');
        static::assertNotNull($IAP1);
        static::assertSame(1, $IAP1->quantity);
        static::assertNull($IAP1->nextBookingDate);

        $IAP2 = $IAPs->get('swagInAppPurchase2');
        static::assertNotNull($IAP2);
        static::assertSame(2, $IAP2->quantity);
        static::assertNotNull($IAP2->nextBookingDate);
        static::assertSame('2021-01-01 00:00:00', $IAP2->nextBookingDate->format('Y-m-d H:i:s'));
    }

    public function testDecodePurchasesRetry(): void
    {
        $token = JWKSHelper::encodeIntoToken(
            [],
            [
                'swagInAppPurchase1' => ['identifier' => 'swagInAppPurchase1', 'sub' => 'example.com', 'quantity' => 1],
                'swagInAppPurchase2' => ['identifier' => 'swagInAppPurchase2', 'sub' => 'example.com', 'quantity' => 2, 'nextBookingDate' => '2021-01-01 00:00:00']
            ]
        );

        $shop = new MockShop('shop-id', 'https://example.com', 'secret');

        $fetcher = $this->createMock(SBPStoreKeyFetcher::class);
        $fetcher
            ->expects(static::exactly(2))
            ->method('getKey')
            ->willReturnOnConsecutiveCalls(
                // wrong JWKS
                (new KeySetFactory())->createFromJSON('{"keys": [{"kty": "RSA","n": "yHenasOsOl-Vv2BmpayS1R8l5L-JN99FwaRRKXFssGTjJDwbYdbe3CqTSKqtOfdqZLzE6-bN2-Q1xqZZsgs0_zHNx7EROXNG_uQs1uuGkS6bgGhnq_2d7wzFvCsyI00CDXZxRlGjKAEhvcXormomF1jpUW08Y5tPeUvMSdEZbZxW1ydir-UrMm1RUSgJgSP-sUqLG7kTIJ6SG7cLtF8c8cHcVXFljMyiYLQHYOECj1oklwvfrfaoT3OKdKGumi39rDthXtFa0Aq1OS_P9qfZJ-yXiQlpf2RxRr3Q5EQJ8E9iqrlOndbkSq7eXne2DvvgsiNdyzRWFvxWSPSd9GZXkw","e": "AQAB","kid": "-1xljHNcPM59Qx9OcULA9LS219bsmKCZueVXhdF0N0k","use": "sig","alg": "RS256"}]}'),
                // correct JWKS
                (new KeySetFactory())->createFromJSON(JWKSHelper::getPublicJWKS())
            );

        $provider = new InAppPurchaseProvider($fetcher);
        $IAPs = $provider->decodePurchases($token->toString(), $shop);

        static::assertCount(2, $IAPs);

        $IAP1 = $IAPs->get('swagInAppPurchase1');
        static::assertNotNull($IAP1);
        static::assertSame(1, $IAP1->quantity);
        static::assertNull($IAP1->nextBookingDate);

        $IAP2 = $IAPs->get('swagInAppPurchase2');
        static::assertNotNull($IAP2);
        static::assertSame(2, $IAP2->quantity);
        static::assertNotNull($IAP2->nextBookingDate);
        static::assertSame('2021-01-01 00:00:00', $IAP2->nextBookingDate->format('Y-m-d H:i:s'));
    }

    public function testDecodePurchaseRetryFails(): void
    {
        $token = JWKSHelper::encodeIntoToken(
            [],
            [
                'swagInAppPurchase1' => ['identifier' => 'swagInAppPurchase1', 'sub' => 'example.com', 'quantity' => 1],
                'swagInAppPurchase2' => ['identifier' => 'swagInAppPurchase2', 'sub' => 'example.com', 'quantity' => 2, 'nextBookingDate' => '2021-01-01 00:00:00']
            ]
        );

        $shop = new MockShop('shop-id', 'https://example.com', 'secret');

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(static::once())
            ->method('error')
            ->with('Failed to decode in-app purchases: The token violates some mandatory constraints, details:
- Token signature mismatch');

        $fetcher = $this->createMock(SBPStoreKeyFetcher::class);
        $fetcher
            ->expects(static::once())
            ->method('getKey')
            ->willReturnOnConsecutiveCalls(
                // wrong JWKS
                (new KeySetFactory())->createFromJSON('{"keys": [{"kty": "RSA","n": "yHenasOsOl-Vv2BmpayS1R8l5L-JN99FwaRRKXFssGTjJDwbYdbe3CqTSKqtOfdqZLzE6-bN2-Q1xqZZsgs0_zHNx7EROXNG_uQs1uuGkS6bgGhnq_2d7wzFvCsyI00CDXZxRlGjKAEhvcXormomF1jpUW08Y5tPeUvMSdEZbZxW1ydir-UrMm1RUSgJgSP-sUqLG7kTIJ6SG7cLtF8c8cHcVXFljMyiYLQHYOECj1oklwvfrfaoT3OKdKGumi39rDthXtFa0Aq1OS_P9qfZJ-yXiQlpf2RxRr3Q5EQJ8E9iqrlOndbkSq7eXne2DvvgsiNdyzRWFvxWSPSd9GZXkw","e": "AQAB","kid": "-1xljHNcPM59Qx9OcULA9LS219bsmKCZueVXhdF0N0k","use": "sig","alg": "RS256"}]}'),
            );

        static::expectException(RequiredConstraintsViolated::class);

        $provider = new InAppPurchaseProvider($fetcher, $logger);
        $provider->decodePurchases($token->toString(), $shop, true);
    }
}

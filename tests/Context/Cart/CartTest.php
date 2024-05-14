<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\Cart;

#[CoversClass(Cart::class)]
class CartTest extends TestCase
{
    public function testConstruct(): void
    {
        $cart = new Cart([
            'token' => 'testToken',
            'customerComment' => 'testCustomerComment',
            'affiliateCode' => 'testAffiliateCode',
            'campaignCode' => 'testCampaignCode',
            'lineItems' => [['id' => 'foo']],
            'deliveries' => [['deliveryDate' => ['earliest' => '2021-01-01T00:00:00Z', 'latest' => '2021-01-02T00:00:00Z']]],
            'transactions' => [['paymentMethodId' => 'baz']],
            'price' => [
                'netPrice' => 100.0,
            ],
        ]);

        self::assertSame('testToken', $cart->getToken());
        self::assertSame('testCustomerComment', $cart->getCustomerComment());
        self::assertSame('testAffiliateCode', $cart->getAffiliateCode());
        self::assertSame('testCampaignCode', $cart->getCampaignCode());
        self::assertCount(1, $cart->getLineItems());
        static::assertSame('foo', $cart->getLineItems()->first()?->getId());
        self::assertCount(1, $cart->getDeliveries());
        static::assertSame('2021-01-01T00:00:00+00:00', $cart->getDeliveries()->first()?->getDeliveryDate()->getEarliest()->format(\DateTime::ATOM));
        static::assertSame('2021-01-02T00:00:00+00:00', $cart->getDeliveries()->first()?->getDeliveryDate()->getLatest()->format(\DateTime::ATOM));
        self::assertCount(1, $cart->getTransactions());
        static::assertSame('baz', $cart->getTransactions()->first()?->getPaymentMethodId());
        self::assertSame(100.0, $cart->getPrice()->getNetPrice());
    }
}

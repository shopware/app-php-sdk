<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\Cart;
use Shopware\App\SDK\Context\Cart\CartPrice;
use Shopware\App\SDK\Context\Cart\CartTransaction;
use Shopware\App\SDK\Context\Cart\Delivery;
use Shopware\App\SDK\Context\Cart\LineItem;

#[CoversClass(Cart::class)]
class CartTest extends TestCase
{
    public function testConstruct(): void
    {
        $lineItems = [
            [
                'uniqueIdentifier' => 'unique-identifier',
                'type' => 'product',
                'referencedId' => 'referenced-id',
                'label' => 'label',
                'good' => true,
                'quantity' => 1,
                'payload' => [
                    'foo' => 'bar',
                ],
                'price' => [
                    'unitPrice' => 1.0,
                    'totalPrice' => 1.0,
                    'calculatedTaxes' => [],
                    'taxRules' => [],
                ],
                'states' => [
                    'foo' => 'bar',
                ],
                'children' => [
                    [
                        'id' => 'foo',
                        'good' => true,
                    ],
                    [
                        'id' => 'bar',
                        'good' => false,
                    ],
                ],
            ],
        ];

        $price = [
            'netPrice' => 1.0,
            'totalPrice' => 1.19,
            'calculatedTaxes' => [
                [
                    'taxRate' => 19.0,
                    'tax' => 0.19,
                ],
            ],
            'taxStatus' => 'gross',
            'taxRules' => [
                [
                    'price' => 1.0,
                    'taxRate' => 19.0,
                    'percentage' => 100.0,
                ],
            ],
            'positionPrice' => 1.0,
            'rawTotal' => 1.0,
        ];

        $deliveries = [
            ['id' => 'foo'],
            ['id' => 'bar'],
        ];

        $transactions = [
            ['id' => 'foo'],
            ['id' => 'bar'],
        ];

        $cart = new Cart([
            'token' => 'token',
            'customerComment' => 'customer-comment',
            'affiliateCode' => 'affiliate-code',
            'campaignCode' => 'campaign-code',
            'lineItems' => $lineItems,
            'deliveries' => $deliveries,
            'transactions' => $transactions,
            'price' => $price,
        ]);

        static::assertSame('token', $cart->getToken());
        static::assertSame('customer-comment', $cart->getCustomerComment());
        static::assertSame('affiliate-code', $cart->getAffiliateCode());
        static::assertSame('campaign-code', $cart->getCampaignCode());
        static::assertEquals([new LineItem($lineItems[0])], $cart->getLineItems());
        static::assertEquals([new Delivery($transactions[0]), new Delivery($transactions[1])], $cart->getDeliveries());
        static::assertEquals([new CartTransaction($transactions[0]), new CartTransaction($transactions[1])], $cart->getTransactions());
        static::assertEquals(new CartPrice($price), $cart->getPrice());
    }
}

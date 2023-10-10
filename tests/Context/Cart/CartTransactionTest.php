<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\CalculatedPrice;
use Shopware\App\SDK\Context\Cart\CartTransaction;

#[CoversClass(CartTransaction::class)]
class CartTransactionTest extends TestCase
{
    public function testConstruct(): void
    {
        $amount = [
            'totalPrice' => 100,
            'currency' => 'EUR',
            'calculatedTaxes' => [
                [
                    'taxRate' => 19,
                    'tax' => 19,
                    'price' => 100,
                ],
            ],
            'taxRules' => [
                [
                    'taxRate' => 19,
                    'percentage' => 100,
                ],
            ],
            'positionPrice' => 100,
            'positionPriceDefinition' => [
                'type' => 'quantity',
                'precision' => 2,
                'taxRules' => [
                    [
                        'taxRate' => 19,
                        'percentage' => 100,
                    ],
                ],
            ],
            'referencePrice' => 100,
            'listPrice' => 100,
            'unitPrice' => 100,
            'quantity' => 1,
            'taxStatus' => 'gross',
        ];

        $transaction = new CartTransaction(
            [
                'paymentMethodId' => 'paymentMethodId',
                'amount' => $amount,
            ]
        );

        static::assertSame('paymentMethodId', $transaction->getPaymentMethodId());
        static::assertEquals(new CalculatedPrice($amount), $transaction->getAmount());
    }
}

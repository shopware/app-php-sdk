<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Context\Cart\CartTransaction;
use PHPUnit\Framework\TestCase;

#[CoversClass(CartTransaction::class)]
class CartTransactionTest extends TestCase
{
    public function testConstruct(): void
    {
        $cartTransaction = new CartTransaction([
            'paymentMethodId' => 'baz',
            'amount' => [
                'unitPrice' => 100.0,
            ],
        ]);

        static::assertSame('baz', $cartTransaction->getPaymentMethodId());
        static::assertSame(100.0, $cartTransaction->getAmount()->getUnitPrice());
    }
}

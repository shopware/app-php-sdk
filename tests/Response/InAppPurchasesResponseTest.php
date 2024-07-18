<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Response;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Response\InAppPurchasesResponse;
use PHPUnit\Framework\TestCase;

#[CoversClass(InAppPurchasesResponse::class)]
class InAppPurchasesResponseTest extends TestCase
{
    public function testPaid(): void
    {
        $purchases = ['foo', 'bar'];
        $response = InAppPurchasesResponse::filter($purchases);

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"purchases":["foo","bar"]}', $response->getBody()->getContents());
    }
}

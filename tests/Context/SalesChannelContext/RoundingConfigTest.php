<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\RoundingConfig;

#[CoversClass(RoundingConfig::class)]
class RoundingConfigTest extends TestCase
{
    public function testConstruct(): void
    {
        $roundingConfig = new RoundingConfig([
            'decimals' => 2,
            'interval' => 0.01,
            'roundForNet' => true,
        ]);

        static::assertSame(2, $roundingConfig->getDecimals());
        static::assertSame(0.01, $roundingConfig->getInterval());
        static::assertTrue($roundingConfig->isRoundForNet());
    }
}

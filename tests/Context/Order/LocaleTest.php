<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Order;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Order\Locale;

#[CoversClass(Locale::class)]
class LocaleTest extends TestCase
{
    public function testGetCode(): void
    {
        $locale = new Locale(['code' => 'de-DE']);
        static::assertSame('de-DE', $locale->getCode());
    }
}

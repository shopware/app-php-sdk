<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Order;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Order\Locale;

#[CoversClass(Locale::class)]
class LocaleTest extends TestCase
{
    public function testGetId(): void
    {
        $locale = new Locale(['id' => 'locale-id']);
        static::assertSame('locale-id', $locale->getId());
    }

    public function testGetCode(): void
    {
        $locale = new Locale(['code' => 'de-DE']);
        static::assertSame('de-DE', $locale->getCode());
    }

    public function testGetName(): void
    {
        $locale = new Locale(['name' => 'German']);
        static::assertSame('German', $locale->getName());
    }

    public function testGetTerritory(): void
    {
        $locale = new Locale(['territory' => 'Germany']);
        static::assertSame('Germany', $locale->getTerritory());
    }
}

<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Order;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Order\Language;

#[CoversClass(Language::class)]
class LanguageTest extends TestCase
{
    public function testGetId(): void
    {
        $language = new Language(['id' => 'language-id']);
        static::assertSame('language-id', $language->getId());
    }

    public function testGetName(): void
    {
        $language = new Language(['name' => 'Deutsch']);
        static::assertSame('Deutsch', $language->getName());
    }

    public function testGetLocale(): void
    {
        $language = new Language(['locale' => ['code' => 'de-DE']]);
        static::assertSame('de-DE', $language->getLocale()?->getCode());
    }

    public function testGetLocaleNull(): void
    {
        $language = new Language([]);
        static::assertNull($language->getLocale());
    }

    public function testGetTranslationCode(): void
    {
        $language = new Language(['translationCode' => ['code' => 'de-AT']]);
        static::assertSame('de-AT', $language->getTranslationCode()?->getCode());
    }

    public function testGetTranslationCodeNull(): void
    {
        $language = new Language([]);
        static::assertNull($language->getTranslationCode());
    }
}

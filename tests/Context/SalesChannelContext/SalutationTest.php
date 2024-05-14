<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\Salutation;

#[CoversClass(Salutation::class)]
class SalutationTest extends TestCase
{
    public function testConstruct(): void
    {
        $salutation = new Salutation([
            'id' => 'salutation-id',
            'displayName' => 'display-name',
            'letterName' => 'letter-name',
            'salutationKey' => 'salutation-key',
        ]);

        static::assertSame('salutation-id', $salutation->getId());
        static::assertSame('salutation-key', $salutation->getSalutationKey());
        static::assertSame('display-name', $salutation->getDisplayName());
        static::assertSame('letter-name', $salutation->getLetterName());
    }
}

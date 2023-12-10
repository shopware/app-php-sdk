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
            'displayName' => 'displayName',
            'letterName' => 'letterName',
            'salutationKey' => 'salutationKey',
        ]);

        static::assertSame('salutation-id', $salutation->getId());
        static::assertSame('displayName', $salutation->getDisplayName());
        static::assertSame('letterName', $salutation->getLetterName());
        static::assertSame('salutationKey', $salutation->getSalutationKey());
    }
}

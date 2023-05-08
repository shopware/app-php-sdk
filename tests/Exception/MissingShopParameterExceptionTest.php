<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Exception\MissingShopParameterException;

#[CoversClass(MissingShopParameterException::class)]
class MissingShopParameterExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $e = new MissingShopParameterException();
        static::assertSame('Missing shop parameters', $e->getMessage());
    }
}

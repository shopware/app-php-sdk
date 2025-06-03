<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Response;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Response\ResponseStruct;

#[CoversClass(ResponseStruct::class)]
class ResponseStructTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $sub = new class () extends ResponseStruct {
            public string $subFoo = 'subBar';
            public string $subBat = 'subBaz';
        };

        $struct = new class ($sub) extends ResponseStruct {
            public string $foo = 'bar';
            public string $bat = 'baz';

            public function __construct(public ResponseStruct $sub)
            {
            }
        };

        static::assertSame(
            [
                'foo' => 'bar',
                'bat' => 'baz',
                'sub' => [
                    'subFoo' => 'subBar',
                    'subBat' => 'subBaz',
                ],
            ],
            $struct->jsonSerialize()
        );
    }
}

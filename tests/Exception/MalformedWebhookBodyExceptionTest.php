<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Exception\MalformedWebhookBodyException;

#[CoversClass(MalformedWebhookBodyException::class)]
class MalformedWebhookBodyExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $e = new MalformedWebhookBodyException();
        static::assertSame('Malformed webhook body, cannot parse body', $e->getMessage());
    }
}

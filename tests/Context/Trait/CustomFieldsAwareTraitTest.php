<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Trait;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Trait\CustomFieldsAware;
use Shopware\App\SDK\Tests\Context\_fixtures\TestCustomFieldsAware;

#[CoversClass(CustomFieldsAware::class)]
class CustomFieldsAwareTraitTest extends TestCase
{
    public function testGetCustomFields(): void
    {
        $test = new TestCustomFieldsAware([
            'customFields' => [
                'foo' => 'bar',
                'bar' => 'foo',
            ],
        ]);

        static::assertSame(['foo' => 'bar', 'bar' => 'foo'], $test->getCustomFields());
    }
}

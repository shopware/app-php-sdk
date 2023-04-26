<?php

namespace Shopware\App\SDK\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\AppConfiguration;

#[CoversClass(AppConfiguration::class)]
class AppConfigurationTest extends TestCase
{
    public function testStruct(): void
    {
        $config = new AppConfiguration(
            'My App',
            'my-secret',
            'https://my-app.com'
        );

        static::assertSame('My App', $config->getAppName());
        static::assertSame('my-secret', $config->getAppSecret());
        static::assertSame('https://my-app.com', $config->getAppUrl());
    }
}

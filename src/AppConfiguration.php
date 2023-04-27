<?php

declare(strict_types=1);

namespace Shopware\App\SDK;

class AppConfiguration
{
    public function __construct(
        private readonly string $appName,
        private readonly string $appSecret
    ) {
    }

    public function getAppName(): string
    {
        return $this->appName;
    }

    public function getAppSecret(): string
    {
        return $this->appSecret;
    }
}

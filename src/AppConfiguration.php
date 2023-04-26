<?php

namespace Shopware\App\SDK;

class AppConfiguration
{
    public function __construct(
        private readonly string $appName,
        private readonly string $appSecret,
        private readonly string $appUrl
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

    public function getAppUrl(): string
    {
        return $this->appUrl;
    }
}

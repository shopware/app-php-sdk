<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context;

class ActionSource
{
    /**
     * @param string $url The shop url
     * @param string $appVersion The installed App version
     */
    public function __construct(public readonly string $url, public readonly string $appVersion)
    {
    }
}

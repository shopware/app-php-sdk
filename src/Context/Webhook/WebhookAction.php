<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Webhook;

use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Shop\ShopInterface;

class WebhookAction
{
    /**
     * @param array<mixed> $payload
     */
    public function __construct(
        public readonly ShopInterface $shop,
        public readonly ActionSource $source,
        public readonly string $eventName,
        public readonly array $payload,
        public readonly \DateTimeInterface $timestamp
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\InAppPurchase;

final class InAppPurchase
{
    public function __construct(
        public readonly string $key,
        public readonly int $quantity,
        public readonly ?\DateTime $nextBookingDate = null,
    ) {
    }
}

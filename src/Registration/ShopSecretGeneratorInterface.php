<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Registration;

interface ShopSecretGeneratorInterface
{
    /**
     * Generate a unique shop secret for a shop
     */
    public function generate(): string;
}

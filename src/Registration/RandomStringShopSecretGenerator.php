<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Registration;

/**
 * @codeCoverageIgnore
 * @infection-ignore-all
 */
class RandomStringShopSecretGenerator implements ShopSecretGeneratorInterface
{
    public function generate(): string
    {
        return bin2hex(random_bytes(64));
    }
}

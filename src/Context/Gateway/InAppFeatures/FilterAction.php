<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Gateway\InAppFeatures;

use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Shop\ShopInterface;

class FilterAction
{
    /**
     * @param Collection<string> $purchases
     */
    public function __construct(
        public readonly ShopInterface $shop,
        public readonly ActionSource $source,
        public readonly Collection $purchases,
    ) {
    }
}

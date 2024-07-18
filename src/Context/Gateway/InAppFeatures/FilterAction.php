<?php

namespace Shopware\App\SDK\Context\Gateway\InAppFeatures;

use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Shop\ShopInterface;

class FilterAction
{
    /**
     * @param Collection<string> $features
     */
    public function __construct(
        public readonly ShopInterface $shop,
        public readonly Collection $features,
    ) {
    }
}
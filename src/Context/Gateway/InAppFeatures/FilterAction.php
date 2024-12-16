<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Gateway\InAppFeatures;

use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Shop\ShopInterface;

class FilterAction
{
    /**
     * Use this action to filter in-app purchases to be *available* to buy for the customer.
     * In the ActionSource you can find any *active* purchases.
     *
     * @param Collection<string> $purchases - The list of purchases to filter for
     */
    public function __construct(
        public readonly ShopInterface $shop,
        public readonly ActionSource $source,
        public readonly Collection $purchases,
    ) {
    }
}

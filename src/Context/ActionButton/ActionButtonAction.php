<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\ActionButton;

use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Shop\ShopInterface;

/**
 * This class represents the action button response
 */
class ActionButtonAction
{
    /**
     * @param array<string> $ids
     */
    public function __construct(
        public readonly ShopInterface $shop,
        public readonly ActionSource $source,
        public readonly array $ids,
        public readonly string $entity,
        public readonly string $action,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Exception;

class ShopNotFoundException extends \RuntimeException
{
    public function __construct(
        private readonly string $shopId,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(sprintf('Shop with id "%s" not found', $shopId), 0, $previous);
    }

    public function getShopId(): string
    {
        return $this->shopId;
    }
}

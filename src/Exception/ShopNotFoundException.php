<?php

namespace Shopware\App\SDK\Exception;

class ShopNotFoundException extends \RuntimeException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('Shop not found', 0, $previous);
    }
}

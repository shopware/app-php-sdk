<?php

declare(strict_types=1);

namespace Shopware\AppSDK\Exception;

use Shopware\AppSDK\Shop\ShopInterface;

class RegistrationNotCompletedException extends \Exception
{
    public function __construct(ShopInterface $shop, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Registration for shop with id %s and url %s is not completed.', $shop->getShopId(), $shop->getShopUrl()),
            0,
            $previous
        );
    }
}

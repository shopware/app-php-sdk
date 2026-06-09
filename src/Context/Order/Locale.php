<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Order;

use Shopware\App\SDK\Context\ArrayStruct;

class Locale extends ArrayStruct
{
    public function getCode(): string
    {
        \assert(is_string($this->data['code']));
        return $this->data['code'];
    }
}

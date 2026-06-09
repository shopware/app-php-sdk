<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Order;

use Shopware\App\SDK\Context\ArrayStruct;

class Locale extends ArrayStruct
{
    public function getId(): string
    {
        \assert(is_string($this->data['id']));
        return $this->data['id'];
    }

    public function getCode(): string
    {
        \assert(is_string($this->data['code']));
        return $this->data['code'];
    }

    public function getName(): string
    {
        \assert(is_string($this->data['name']));
        return $this->data['name'];
    }

    public function getTerritory(): string
    {
        \assert(is_string($this->data['territory']));
        return $this->data['territory'];
    }
}

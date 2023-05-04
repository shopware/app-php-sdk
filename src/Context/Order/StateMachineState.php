<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Order;

use Shopware\App\SDK\Context\ArrayStruct;

class StateMachineState extends ArrayStruct
{
    public function getId(): string
    {
        \assert(\is_string($this->data['id']));
        return $this->data['id'];
    }

    public function getTechnicalName(): string
    {
        \assert(\is_string($this->data['technicalName']));
        return $this->data['technicalName'];
    }
}

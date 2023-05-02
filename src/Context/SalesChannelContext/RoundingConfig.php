<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\SalesChannelContext;

use Shopware\App\SDK\Context\ArrayStruct;

class RoundingConfig extends ArrayStruct
{
    /**
     * How many decimals should be used for rounding
     */
    public function getDecimals(): int
    {
        \assert(is_int($this->data['decimals']));
        return $this->data['decimals'];
    }


    /**
     * In which interval should be rounded
     */
    public function getInterval(): float
    {
        \assert(is_float($this->data['interval']));
        return $this->data['interval'];
    }

    public function isRoundForNet(): bool
    {
        \assert(is_bool($this->data['roundForNet']));
        return $this->data['roundForNet'];
    }
}

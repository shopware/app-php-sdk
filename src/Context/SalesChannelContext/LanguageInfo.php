<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\SalesChannelContext;

use Shopware\App\SDK\Context\ArrayStruct;

class LanguageInfo extends ArrayStruct
{
    public function getName(): string
    {
        \assert(\is_string($this->data['name']));

        return $this->data['name'];
    }

    public function getLocaleCode(): string
    {
        \assert(\is_string($this->data['localeCode']));

        return $this->data['localeCode'];
    }
}

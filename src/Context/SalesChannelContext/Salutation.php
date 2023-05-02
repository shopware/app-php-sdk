<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\SalesChannelContext;

use Shopware\App\SDK\Context\ArrayStruct;

class Salutation extends ArrayStruct
{
    public function getId(): string
    {
        \assert(is_string($this->data['id']));
        return $this->data['id'];
    }

    public function getDisplayName(): string
    {
        \assert(is_string($this->data['displayName']));
        return $this->data['displayName'];
    }

    public function getLetterName(): string
    {
        \assert(is_string($this->data['letterName']));
        return $this->data['letterName'];
    }

    public function getSalutationKey(): string
    {
        \assert(is_string($this->data['salutationKey']));
        return $this->data['salutationKey'];
    }

}

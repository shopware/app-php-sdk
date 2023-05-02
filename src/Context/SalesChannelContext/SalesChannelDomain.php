<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\SalesChannelContext;

use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\Trait\CustomFieldsAware;

class SalesChannelDomain extends ArrayStruct
{
    use CustomFieldsAware;

    public function getId(): string
    {
        \assert(is_string($this->data['id']));
        return $this->data['id'];
    }

    public function getUrl(): string
    {
        \assert(is_string($this->data['url']));
        return $this->data['url'];
    }

    public function getLanguageId(): string
    {
        \assert(is_string($this->data['languageId']));
        return $this->data['languageId'];
    }

    public function getCurrencyId(): string
    {
        \assert(is_string($this->data['currencyId']));
        return $this->data['currencyId'];
    }

    public function getSnippetSetId(): string
    {
        \assert(is_string($this->data['snippetSetId']));
        return $this->data['snippetSetId'];
    }
}

<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Order;

use Shopware\App\SDK\Context\ArrayStruct;

class Language extends ArrayStruct
{
    public function getId(): string
    {
        \assert(is_string($this->data['id']));
        return $this->data['id'];
    }

    public function getName(): string
    {
        \assert(is_string($this->data['name']));
        return $this->data['name'];
    }

    public function getLocale(): ?Locale
    {
        if (!$this->isset('locale')) {
            return null;
        }

        \assert(is_array($this->data['locale']));
        return new Locale($this->data['locale']);
    }

    public function getTranslationCode(): ?Locale
    {
        if (!$this->isset('translationCode')) {
            return null;
        }

        \assert(is_array($this->data['translationCode']));
        return new Locale($this->data['translationCode']);
    }
}

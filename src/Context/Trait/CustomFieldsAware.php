<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Trait;

trait CustomFieldsAware
{
    /**
     * @return array<mixed>
     */
    public function getCustomFields(): array
    {
        \assert(\is_array($this->data['customFields']) || $this->data['customFields'] === null);
        return $this->data['customFields'] ?? [];
    }
}

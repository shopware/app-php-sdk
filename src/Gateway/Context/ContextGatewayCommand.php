<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Gateway\Context;

abstract class ContextGatewayCommand implements \JsonSerializable
{
    /**
     * @var array<string, mixed>
     */
    public array $payload = [];

    protected string $keyName;

    public function getKey(): string
    {
        return $this->keyName;
    }

    public function setPayloadValue(string $key, mixed $value): void
    {
        $this->payload[$key] = $value;
    }

    public function hasPayloadValue(string $key): bool
    {
        return isset($this->payload[$key]);
    }

    public function getPayloadValue(string $key): mixed
    {
        if (!$this->hasPayloadValue($key)) {
            return null;
        }

        return $this->payload[$key];
    }

    /**
     * @return array{command: string, payload: array<string, mixed>}
     */
    public function jsonSerialize(): array
    {
        return [
            'command' => $this->keyName,
            'payload' => $this->payload,
        ];
    }
}

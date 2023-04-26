<?php

declare(strict_types=1);

namespace Shopware\AppSDK\Exception;

class AuthenticationException extends \RuntimeException
{
    public function __construct(private readonly string $shopUrl, private readonly string $apiKey, private readonly string $reason, ?\Throwable $previous = null)
    {
        $message = sprintf('Could not authenticate with store. Shopurl: %s, apikey: %s, reason: %s', $shopUrl, $apiKey, $reason);

        parent::__construct($message, 0, $previous);
    }

    public function getShopUrl(): string
    {
        return $this->shopUrl;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }
}

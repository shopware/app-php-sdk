<?php

declare(strict_types=1);

namespace Shopware\App\SDK\HttpClient\Exception;

use Psr\Http\Message\ResponseInterface;

class AuthenticationFailedException extends \RuntimeException
{
    public function __construct(string $shopId, private readonly ResponseInterface $response, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('Authentication failed for shop %s', $shopId), 0, $previous);
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}

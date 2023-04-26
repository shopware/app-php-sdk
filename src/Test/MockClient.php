<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Test;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MockClient implements ClientInterface
{
    /**
     * @param array<ResponseInterface> $responses
     */
    public function __construct(private array $responses)
    {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $response = array_shift($this->responses);

        if ($response === null) {
            throw new \RuntimeException('No more responses available');
        }

        return $response;
    }

    public function isEmpty(): bool
    {
        return count($this->responses) === 0;
    }
}

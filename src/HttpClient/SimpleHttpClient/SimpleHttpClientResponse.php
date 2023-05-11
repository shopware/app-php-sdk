<?php

declare(strict_types=1);

namespace Shopware\App\SDK\HttpClient\SimpleHttpClient;

use Psr\Http\Message\ResponseInterface;

class SimpleHttpClientResponse
{
    public function __construct(private readonly ResponseInterface $response)
    {
    }

    public function getContent(): string
    {
        $contents = $this->response->getBody()->getContents();
        $this->response->getBody()->rewind();

        return $contents;
    }

    /**
     * @return array<mixed>
     */
    public function json(): array
    {
        $data = \json_decode($this->getContent(), true, flags: JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new \RuntimeException('Response is not a valid JSON array');
        }

        return $data;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getHeader(string $name): string
    {
        return $this->response->getHeaderLine($name);
    }

    public function getRawResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function ok(): bool
    {
        return $this->getStatusCode() >= 200 && $this->getStatusCode() < 300;
    }
}

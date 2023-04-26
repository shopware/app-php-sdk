<?php

declare(strict_types=1);

namespace Shopware\App\SDK\HttpClient;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class LoggerClient implements ClientInterface
{
    public function __construct(private readonly ClientInterface $client, private readonly LoggerInterface $logger)
    {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $response = $this->client->sendRequest($request);

        $this->logger->debug('Request body', ['body' => $request->getBody()->getContents()]);
        $request->getBody()->rewind();

        $this->logger->info(sprintf('Request: %s %s', $request->getMethod(), $request->getUri()), [
            'request' => [
                'method' => $request->getMethod(),
                'uri' => $request->getUri(),
                'headers' => $request->getHeaders(),
            ],
            'response' => [
                'status' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
            ]
        ]);

        $this->logger->debug('Response body', ['body' => $response->getBody()->getContents()]);
        $response->getBody()->rewind();

        return $response;
    }
}

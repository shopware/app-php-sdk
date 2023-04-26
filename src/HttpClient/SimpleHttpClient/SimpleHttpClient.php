<?php

declare(strict_types=1);

namespace Shopware\App\SDK\HttpClient\SimpleHttpClient;

use Http\Discovery\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\PsrFactory;

class SimpleHttpClient
{
    public function __construct(private readonly ClientInterface $client)
    {
    }

    /**
     * @param array<string, string> $headers
     */
    public function get(string $url, array $headers = []): Response
    {
        $request = $this->createRequest('GET', $url, $headers);

        $response = $this->client->sendRequest($request);

        return new Response($response);
    }

    /**
     * @param array<mixed> $body
     * @param array<string, string> $headers
     */
    public function post(string $url, array $body = [], array $headers = []): Response
    {
        return $this->doRequest('POST', $url, $body, $headers);
    }

    /**
     * @param array<mixed> $body
     * @param array<string, string> $headers
     */
    public function patch(string $url, array $body = [], array $headers = []): Response
    {
        return $this->doRequest('PATCH', $url, $body, $headers);
    }

    /**
     * @param array<mixed> $body
     * @param array<string, string> $headers
     */
    public function put(string $url, array $body = [], array $headers = []): Response
    {
        return $this->doRequest('PUT', $url, $body, $headers);
    }

    /**
     * @param array<mixed> $body
     * @param array<string, string> $headers
     */
    public function delete(string $url, array $body = [], array $headers = []): Response
    {
        return $this->doRequest('DELETE', $url, $body, $headers);
    }


    /**
     * @param array<mixed> $body
     * @param array<string, string> $headers
     */
    private function doRequest(string $method, string $url, array $body = [], array $headers = []): Response
    {
        $factory = new Psr17Factory();

        $request = $this->createRequest($method, $url, $headers);
        $request = $request->withBody(
            $factory->createStream(json_encode($body, JSON_THROW_ON_ERROR))
        );

        $response = $this->client->sendRequest($request);

        return new Response($response);
    }

    /**
     * @param array<string, string> $headers
     */
    private function createRequest(string $method, string $url, array $headers = []): RequestInterface
    {
        $factory = new Psr17Factory();
        $request = $factory->createRequest($method, $url);

        // will be overwritten by the headers passed in the arguments
        $request = $request
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/json');

        foreach ($headers as $headerName => $headerValue) {
            $request = $request->withHeader($headerName, $headerValue);
        }

        return $request;
    }
}

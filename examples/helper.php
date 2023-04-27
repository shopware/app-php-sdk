<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;

function send(ResponseInterface $response): void
{
    $http_line = sprintf(
        'HTTP/%s %s %s',
        $response->getProtocolVersion(),
        $response->getStatusCode(),
        $response->getReasonPhrase()
    );
    header($http_line, true, $response->getStatusCode());
    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header("$name: $value", false);
        }
    }
    $stream = $response->getBody();
    if ($stream->isSeekable()) {
        $stream->rewind();
    }
    while (!$stream->eof()) {
        echo $stream->read(1024 * 8);
    }
}

<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Framework;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Decodes the JSON body of an incoming request.
 *
 * When the surrounding framework already parsed the body of a PSR-7 server request into
 * an array, that result is reused.
 *
 * @internal
 */
final class RequestBodyParser
{
    /**
     * @return array<array-key, mixed>|null
     *
     * @throws \JsonException when the body is not valid JSON
     */
    public static function parse(RequestInterface $request): ?array
    {
        if ($request instanceof ServerRequestInterface) {
            $body = $request->getParsedBody();

            if (\is_array($body)) {
                return $body;
            }
        }

        $body = \json_decode($request->getBody()->getContents(), true, flags: \JSON_THROW_ON_ERROR);
        $request->getBody()->rewind();

        return \is_array($body) ? $body : null;
    }
}

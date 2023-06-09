<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Exception;

use Psr\Http\Message\RequestInterface;

class SignatureInvalidException extends \Exception
{
    public function __construct(
        private readonly RequestInterface $request,
        ?\Throwable $previous = null
    ) {
        parent::__construct('Signature could not be verified', 0, $previous);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}

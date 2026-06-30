<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Exception;

use Psr\Http\Message\RequestInterface;

class SignatureInvalidException extends \Exception
{
    public function __construct(
        private readonly RequestInterface $request,
        ?\Throwable $previous = null,
        /**
         * Which verification leg failed (e.g. app-signature, shop-signature), or null when not tagged.
         */
        public readonly ?string $verificationStage = null
    ) {
        $message = 'Signature could not be verified';
        if ($verificationStage !== null) {
            $message = \sprintf('%s (verification stage: %s)', $message, $verificationStage);
        }

        parent::__construct($message, 0, $previous);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}

<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Exception;

class MissingClaimException extends \RuntimeException
{
    public function __construct(string $claimName)
    {
        parent::__construct(\sprintf('Missing claim "%s", did you forgot to add permissions in your app to this?', $claimName));
    }
}

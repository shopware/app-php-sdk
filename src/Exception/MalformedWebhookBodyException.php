<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Exception;

class MalformedWebhookBodyException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Malformed webhook body, cannot parse body');
    }
}

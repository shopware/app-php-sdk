<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Exception;

class MissingShopParameterException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Missing shop parameters');
    }
}

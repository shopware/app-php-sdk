<?php

namespace Shopware\App\SDK\Exception;

class MissingShopParameterException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Missing shop parameters');
    }
}

<?php

namespace Shopware\AppSDK\Exception;

class MissingShopParameterException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Missing shop parameters');
    }
}
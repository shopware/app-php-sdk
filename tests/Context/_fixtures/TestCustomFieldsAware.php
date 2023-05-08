<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\_fixtures;

use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\Trait\CustomFieldsAware;

class TestCustomFieldsAware extends ArrayStruct
{
    use CustomFieldsAware;
}

<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Event;

use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\Shop\ShopInterface;

/**
 * This event is fired before the shop has been saved to the database. So you are able to modify the shop before it is saved.
 */
class RegistrationBeforeCompletedEvent extends AbstractAppLifecycleEvent
{
    /**
     * @param array{apiKey: string, secretKey: string} $confirmation
     */
    public function __construct(ShopInterface $shop, RequestInterface $request, private readonly array $confirmation)
    {
        parent::__construct($request, $shop);
    }

    /**
     * @return array{apiKey: string, secretKey: string}
     */
    public function getConfirmation(): array
    {
        return $this->confirmation;
    }
}

<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Event;

/**
 * This event is fired when a shop has been finished with the registration. (Already persisted in the database)
 */
class RegistrationCompletedEvent extends AbstractAppLifecycleEvent
{
}

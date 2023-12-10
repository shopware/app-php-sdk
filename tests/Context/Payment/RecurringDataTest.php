<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Payment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Payment\RecurringData;

#[CoversClass(RecurringData::class)]
class RecurringDataTest extends TestCase
{
    public function testConstruct(): void
    {
        $recurring = new RecurringData([
            'subscriptionId' => 'subscriptionId',
            'nextSchedule' => '2021-01-01T00:00:00+00:00',
        ]);

        static::assertSame('subscriptionId', $recurring->getSubscriptionId());
        static::assertEquals(new \DateTime('2021-01-01T00:00:00+00:00'), $recurring->getNextSchedule());
    }
}

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
        $data = [
            'subscriptionId' => '123',
            'nextSchedule' => '2021-01-01T00:00:00Z',
        ];

        $recurringData = new RecurringData($data);

        static::assertSame('123', $recurringData->getSubscriptionId());
        static::assertEquals(new \DateTime('2021-01-01T00:00:00Z'), $recurringData->getNextSchedule());
    }
}

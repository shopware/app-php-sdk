<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Order;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Order\StateMachineState;

#[CoversClass(StateMachineState::class)]
class StateMachineStateTest extends TestCase
{
    public function testConstruct(): void
    {
        $stateMachineState = new StateMachineState([
            'id' => 'state-id',
            'technicalName' => 'technical-name',
        ]);

        static::assertSame('state-id', $stateMachineState->getId());
        static::assertSame('technical-name', $stateMachineState->getTechnicalName());
    }
}

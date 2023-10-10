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
        $stateMachine = new StateMachineState([
            'id' => 'foo',
            'technicalName' => 'test_foo',
        ]);

        static::assertSame('foo', $stateMachine->getId());
        static::assertSame('test_foo', $stateMachine->getTechnicalName());
    }
}

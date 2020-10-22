<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Event\NamedEventInterface;
use App\Helper\NamedEventDispatcher;
use App\Tests\ProphecyTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @coversDefaultClass \App\Helper\NamedEventDispatcher
 */
class NamedEventDispatcherTest extends ProphecyTestCase
{
    /**
     * @var EventDispatcherInterface|ObjectProphecy
     */
    private $eventDispatcher;

    private NamedEventDispatcher $namedEventDispatcher;

    public function setUp(): void
    {
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->namedEventDispatcher = new NamedEventDispatcher($this->eventDispatcher->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::dispatch
     */
    public function testDispatchWithUnnamedEvent(): void
    {
        $event = new Event();

        $this->eventDispatcher->dispatch($event, null)->shouldBeCalled();
        $return = $this->namedEventDispatcher->dispatch($event);

        $this->assertEquals($event, $return);
    }

    /**
     * @covers ::__construct
     * @covers ::dispatch
     */
    public function testDispatchNamedEvent(): void
    {
        $event = new class() extends Event implements NamedEventInterface {
            public function getEventName(): string
            {
                return 'event name';
            }

            public function getContext(): array
            {
                return [];
            }
        };

        $this->eventDispatcher->dispatch($event, NamedEventInterface::class)->willReturn($event)->shouldBeCalled();
        $this->eventDispatcher->dispatch($event, null)->willReturn($event)->shouldBeCalled();
        $return = $this->namedEventDispatcher->dispatch($event);

        $this->assertEquals($event, $return);
    }

    /**
     * @covers ::__construct
     * @covers ::dispatch
     */
    public function testDispatchUnnamedEventWithEventNameAttribute(): void
    {
        $event = new Event();

        $this->eventDispatcher->dispatch($event, '123')->willReturn($event)->shouldBeCalled();
        $return = $this->namedEventDispatcher->dispatch($event, '123');

        $this->assertEquals($event, $return);
    }

    /**
     * @covers ::__construct
     * @covers ::dispatch
     */
    public function testDispatchNamedEventWithEventNameAttribute(): void
    {
        $event = new class() extends Event implements NamedEventInterface {
            public function getEventName(): string
            {
                return 'event name';
            }

            public function getContext(): array
            {
                return [];
            }
        };

        $this->eventDispatcher->dispatch($event, '123')->willReturn($event)->shouldBeCalled();
        $this->eventDispatcher->dispatch($event, NamedEventInterface::class)->willReturn($event)->shouldBeCalled();
        $return = $this->namedEventDispatcher->dispatch($event, '123');

        $this->assertEquals($event, $return);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Event\Cron;

use App\Event\Cron\CronCommandTriggeredEvent;
use App\Tests\ProphecyTestCase;
use Symfony\Component\Console\Command\Command;

/**
 * @coversDefaultClass \App\Event\Cron\CronCommandTriggeredEvent
 */
class CronCommandTriggeredEventTest extends ProphecyTestCase
{
    private CronCommandTriggeredEvent $event;

    public function setUp(): void
    {
        $command = $this->prophesize(Command::class);
        $command->getName()->willReturn('command-name');
        $this->event = new CronCommandTriggeredEvent($command->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::getContext
     */
    public function testGetContext(): void
    {
        $this->assertEquals(['command_name' => 'command-name'], $this->event->getContext());
    }

    /**
     * @covers ::__construct
     * @covers ::getEventName
     */
    public function testGetEventName(): void
    {
        $this->assertEquals('Cron command triggered', $this->event->getEventName());
    }
}

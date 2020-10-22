<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber\Command;

use App\Command\Cron\CronCleanupRoomAvailabilityCommand;
use App\Event\Cron\CronCommandTriggeredEvent;
use App\EventSubscriber\Command\ConsoleCommandEventSubscriber;
use App\Tests\ProphecyTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConsoleCommandEventSubscriberTest extends ProphecyTestCase
{
    /**
     * @var EventDispatcherInterface|ObjectProphecy
     */
    private $eventDispatcher;

    private ConsoleCommandEventSubscriber $consoleCommandEventSubscriber;

    public function setUp(): void
    {
        $this->eventDispatcher = $this->prophesize(EventDispatcher::class);
        $this->consoleCommandEventSubscriber = new ConsoleCommandEventSubscriber($this->eventDispatcher->reveal());
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals(
            [ConsoleCommandEvent::class => ['consoleCommandEvent', 100]],
            ConsoleCommandEventSubscriber::getSubscribedEvents()
        );
    }

    public function testConsoleCommandEventWithNonCronCommand(): void
    {
        $command = $this->prophesize(Command::class);
        $event = new ConsoleCommandEvent(
            $command->reveal(),
            $this->prophesize(InputInterface::class)->reveal(),
            $this->prophesize(OutputInterface::class)->reveal()
        );

        $this->eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();
        $this->consoleCommandEventSubscriber->consoleCommandEvent($event);
    }

    public function testConsoleCommandEventWithNullCommand(): void
    {
        $event = new ConsoleCommandEvent(
            null,
            $this->prophesize(InputInterface::class)->reveal(),
            $this->prophesize(OutputInterface::class)->reveal()
        );

        $this->eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();
        $this->consoleCommandEventSubscriber->consoleCommandEvent($event);
    }

    public function testConsoleCommandEventWithCronCommand(): void
    {
        $command = $this->prophesize(CronCleanupRoomAvailabilityCommand::class);
        $event = new ConsoleCommandEvent(
            $command->reveal(),
            $this->prophesize(InputInterface::class)->reveal(),
            $this->prophesize(OutputInterface::class)->reveal()
        );

        $cronCommandTriggeredEvent = new CronCommandTriggeredEvent($command->reveal());
        $this
            ->eventDispatcher
            ->dispatch($cronCommandTriggeredEvent)
            ->shouldBeCalled()
            ->willReturn($cronCommandTriggeredEvent);

        $this->consoleCommandEventSubscriber->consoleCommandEvent($event);
    }
}

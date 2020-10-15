<?php

declare(strict_types=1);

namespace App\Tests\Command\Cron;

use App\Command\Cron\CronCleanupRoomAvailabilityCommand;
use App\Event\Cleanup\AvailabilityCleanupEvent;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CronCleanupRoomAvailabilityCommandTest extends KernelTestCase
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var CronCleanupRoomAvailabilityCommand
     */
    protected $command;

    /**
     * @var EventDispatcherInterface|ObjectProphecy
     */
    protected $eventDispatcher;

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $this->application = new Application($kernel);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->command = new CronCleanupRoomAvailabilityCommand($this->eventDispatcher->reveal());

        $this->application->add($this->command);
    }

    public function testExecute(): void
    {
        $commandTester = new CommandTester($this->command);

        $this->eventDispatcher->dispatch(Argument::type(AvailabilityCleanupEvent::class))->shouldBeCalled();

        $commandTester->execute([]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testCorrectDescription(): void
    {
        $this->assertEquals('Run room-availability cleanup routines', $this->command->getDescription());
    }

    public function testCorrectCommand(): void
    {
        $this->assertEquals('r2d2:cleanup:room-availability', $this->command->getName());
    }
}

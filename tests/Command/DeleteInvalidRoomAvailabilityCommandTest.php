<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\DeleteInvalidRoomAvailabilityCommand;
use App\Contract\Message\InvalidAvailabilityCleanup;
use App\Tests\ProphecyKernelTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class DeleteInvalidRoomAvailabilityCommandTest extends ProphecyKernelTestCase
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var DeleteInvalidRoomAvailabilityCommand
     */
    protected $command;

    /**
     * @var MessageBusInterface|ObjectProphecy
     */
    protected $messageBus;

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $this->application = new Application($kernel);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->command = new DeleteInvalidRoomAvailabilityCommand($this->messageBus->reveal());

        $this->application->add($this->command);
    }

    public function testExecute(): void
    {
        $commandTester = new CommandTester($this->command);

        $this
            ->messageBus
            ->dispatch(Argument::type(InvalidAvailabilityCleanup::class))
            ->shouldBeCalled()
            ->willReturn(new Envelope(new \stdClass()));

        $commandTester->execute([]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testCorrectDescription(): void
    {
        $this->assertEquals('Starts deleting invalid room availabilities', $this->command->getDescription());
    }

    public function testCorrectCommand(): void
    {
        $this->assertEquals('r2d2:cleanup:invalid-room-availability', $this->command->getName());
    }
}

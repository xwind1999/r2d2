<?php

declare(strict_types=1);

namespace App\Tests\Handler\Cleanup;

use App\Contract\Message\InvalidAvailabilityCleanup;
use App\Handler\Cleanup\InvalidRoomAvailabilityCleanupHandler;
use App\Repository\RoomAvailabilityRepository;
use App\Tests\ProphecyTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \App\Handler\Cleanup\InvalidRoomAvailabilityCleanupHandler
 */
class InvalidRoomAvailabilityCleanupHandlerTest extends ProphecyTestCase
{
    /**
     * @var ObjectProphecy|RoomAvailabilityRepository
     */
    private $roomAvailabilityRepository;

    /**
     * @var MessageBusInterface|ObjectProphecy
     */
    private $messageBus;

    private InvalidRoomAvailabilityCleanupHandler $handler;

    public function setUp(): void
    {
        $this->roomAvailabilityRepository = $this->prophesize(RoomAvailabilityRepository::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);

        $this->handler = new InvalidRoomAvailabilityCleanupHandler(
            $this->roomAvailabilityRepository->reveal(),
            $this->messageBus->reveal()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testInvokeWillEndProcessingAndWontDispatchAnotherMessage(): void
    {
        $this->roomAvailabilityRepository->cleanupInvalid()->willReturn(0);

        $this->messageBus->dispatch(Argument::any())->shouldNotBeCalled();

        $this->handler->__invoke(new InvalidAvailabilityCleanup());
    }

    /**
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testInvokeWillDispatchAnotherMessage(): void
    {
        $event = new InvalidAvailabilityCleanup();
        $this->roomAvailabilityRepository->cleanupInvalid()->willReturn(31);

        $this->messageBus->dispatch(Argument::is($event))->shouldBeCalled()->willReturn(new Envelope($event));

        $this->handler->__invoke($event);
    }
}

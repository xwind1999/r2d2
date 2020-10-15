<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber\Cleanup;

use App\Event\Cleanup\AvailabilityCleanupEvent;
use App\EventSubscriber\Cleanup\CleanupRoomAvailabilityEventSubscriber;
use App\Repository\RoomAvailabilityRepository;
use PHPUnit\Framework\TestCase;

class CleanupRoomAvailabilityEventSubscriberTest extends TestCase
{
    private CleanupRoomAvailabilityEventSubscriber $eventSubscriber;

    private $roomAvailabilityRepository;

    public function setUp(): void
    {
        $this->roomAvailabilityRepository = $this->prophesize(RoomAvailabilityRepository::class);
        $this->eventSubscriber = new CleanupRoomAvailabilityEventSubscriber($this->roomAvailabilityRepository->reveal());
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals(
            [AvailabilityCleanupEvent::class => ['handleMessage']],
            CleanupRoomAvailabilityEventSubscriber::getSubscribedEvents()
        );
    }

    public function testHandleMessage(): void
    {
        $this->roomAvailabilityRepository->cleanUp()->shouldBeCalled();
        $this->eventSubscriber->handleMessage(new AvailabilityCleanupEvent());
    }
}

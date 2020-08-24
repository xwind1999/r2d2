<?php

declare(strict_types=1);

namespace App\EventSubscriber\Cleanup;

use App\Event\Cleanup\CleanupRoomAvailabilityEvent;
use App\Repository\RoomAvailabilityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CleanupRoomAvailabilityEventSubscriber implements EventSubscriberInterface
{
    private RoomAvailabilityRepository $roomAvailabilityRepository;

    public function __construct(RoomAvailabilityRepository $roomAvailabilityRepository)
    {
        $this->roomAvailabilityRepository = $roomAvailabilityRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CleanupRoomAvailabilityEvent::class => ['handleMessage'],
        ];
    }

    public function handleMessage(CleanupRoomAvailabilityEvent $event): void
    {
        $this->roomAvailabilityRepository->cleanUp();
    }
}

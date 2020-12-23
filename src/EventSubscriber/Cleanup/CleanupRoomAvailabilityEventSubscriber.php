<?php

declare(strict_types=1);

namespace App\EventSubscriber\Cleanup;

use App\Event\Cleanup\AvailabilityCleanupEvent;
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
            AvailabilityCleanupEvent::class => ['handleMessage'],
        ];
    }

    public function handleMessage(): void
    {
        $this->roomAvailabilityRepository->cleanUp();
    }
}

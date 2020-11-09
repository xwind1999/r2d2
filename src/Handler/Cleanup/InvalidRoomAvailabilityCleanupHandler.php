<?php

declare(strict_types=1);

namespace App\Handler\Cleanup;

use App\Contract\Message\InvalidAvailabilityCleanup;
use App\Repository\RoomAvailabilityRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class InvalidRoomAvailabilityCleanupHandler implements MessageHandlerInterface
{
    private RoomAvailabilityRepository $roomAvailabilityRepository;

    private MessageBusInterface $messageBus;

    public function __construct(
        RoomAvailabilityRepository $roomAvailabilityRepository,
        MessageBusInterface $messageBus
    ) {
        $this->roomAvailabilityRepository = $roomAvailabilityRepository;
        $this->messageBus = $messageBus;
    }

    public function __invoke(InvalidAvailabilityCleanup $invalidAvailabilityCleanup): void
    {
        if ((int) $this->roomAvailabilityRepository->cleanupInvalid() > 0) {
            $this->messageBus->dispatch($invalidAvailabilityCleanup);
        }
    }
}

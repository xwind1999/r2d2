<?php

declare(strict_types=1);

namespace App\Exception\Manager\RoomAvailability;

use App\Exception\ContextualException;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;

class OutdatedRoomAvailabilityInformationException extends ContextualException implements UnrecoverableExceptionInterface
{
    protected const CODE = 1500009;
    protected const MESSAGE = 'Outdated room availability information received';
}

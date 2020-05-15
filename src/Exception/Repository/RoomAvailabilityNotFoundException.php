<?php

declare(strict_types=1);

namespace App\Exception\Repository;

class RoomAvailabilityNotFoundException extends EntityNotFoundException
{
    protected const MESSAGE = 'Room availability not found';
    protected const CODE = 1000018;
}

<?php

declare(strict_types=1);

namespace App\Exception\Repository;

class RoomPriceNotFoundException extends EntityNotFoundException
{
    protected const CODE = 1000020;
}

<?php

declare(strict_types=1);

namespace App\Exception\Manager\RoomPrice;

use App\Exception\ContextualException;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;

class OutdatedRoomPriceException extends ContextualException implements UnrecoverableExceptionInterface
{
    protected const CODE = 1500010;
    public const MESSAGE = 'Outdated room price received';
}

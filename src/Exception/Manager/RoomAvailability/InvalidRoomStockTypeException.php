<?php

declare(strict_types=1);

namespace App\Exception\Manager\RoomAvailability;

use App\Exception\ContextualException;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;

class InvalidRoomStockTypeException extends ContextualException implements UnrecoverableExceptionInterface
{
    protected const CODE = 1500010;
    protected const MESSAGE = 'The room stock type from this component is not valid';
}

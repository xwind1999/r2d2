<?php

declare(strict_types=1);

namespace App\Constraint;

class BookingStatusConstraint extends AbstractChoiceConstraint
{
    public const BOOKING_STATUS_CREATED = 'created';
    public const BOOKING_STATUS_COMPLETE = 'complete';
    public const BOOKING_STATUS_CANCELLED = 'cancelled';

    public const VALID_VALUES = [
        self::BOOKING_STATUS_CREATED,
        self::BOOKING_STATUS_COMPLETE,
        self::BOOKING_STATUS_CANCELLED,
    ];
}

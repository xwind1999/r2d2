<?php

declare(strict_types=1);

namespace App\Constraint;

class CMHStatusConstraint extends AbstractChoiceConstraint
{
    public const BOOKING_STATUS_CONFIRMED = 'confirmed';
    public const BOOKING_STATUS_CANCELLED = 'cancelled';
    public const BOOKING_STATUS_REJECTED = 'rejected';

    public const VALID_VALUES = [
        self::BOOKING_STATUS_CONFIRMED,
        self::BOOKING_STATUS_CANCELLED,
        self::BOOKING_STATUS_REJECTED,
    ];
}

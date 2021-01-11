<?php

declare(strict_types=1);

namespace App\Constraint;

class BookingStatusConstraint extends AbstractChoiceConstraint
{
    public const BOOKING_STATUS_CREATED = 'created';
    public const BOOKING_STATUS_COMPLETE = 'complete';
    public const BOOKING_STATUS_CANCELLED = 'cancelled';
    public const BOOKING_STATUS_REJECTED = 'rejected';
    public const BOOKING_STATUS_PENDING_PARTNER_CONFIRMATION = 'pending_partner_confirmation';

    public const VALID_VALUES = [
        self::BOOKING_STATUS_CREATED,
        self::BOOKING_STATUS_COMPLETE,
        self::BOOKING_STATUS_CANCELLED,
        self::BOOKING_STATUS_REJECTED,
        self::BOOKING_STATUS_PENDING_PARTNER_CONFIRMATION,
    ];

    public static function isAnOnRequestStatus(string $bookingStatus): bool
    {
        return BookingStatusConstraint::BOOKING_STATUS_PENDING_PARTNER_CONFIRMATION == $bookingStatus ||
            BookingStatusConstraint::BOOKING_STATUS_REJECTED == $bookingStatus;
    }

    public static function getValidValuesForUpdate(): array
    {
        return [
            self::BOOKING_STATUS_COMPLETE,
            self::BOOKING_STATUS_CANCELLED,
            self::BOOKING_STATUS_REJECTED,
            self::BOOKING_STATUS_PENDING_PARTNER_CONFIRMATION,
        ];
    }
}

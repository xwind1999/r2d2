<?php

declare(strict_types=1);

namespace App\Constraint;

class BookingChannelConstraint extends AbstractChoiceConstraint
{
    public const BOOKING_LAST_STATUS_CHANNEL_CUSTOMER = 'customer';
    public const BOOKING_LAST_STATUS_CHANNEL_JARVIS_BOOKING = 'jarvis-booking';
    public const BOOKING_LAST_STATUS_CHANNEL_PARTNER = 'partner';

    public const VALID_VALUES = [
        self::BOOKING_LAST_STATUS_CHANNEL_CUSTOMER,
        self::BOOKING_LAST_STATUS_CHANNEL_JARVIS_BOOKING,
        self::BOOKING_LAST_STATUS_CHANNEL_PARTNER,
    ];

    public static function getValidValues(): array
    {
        $values = self::VALID_VALUES;
        $values[] = null;

        return $values;
    }
}

<?php

declare(strict_types=1);

namespace App\DBAL;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class BookingStatus extends StringType
{
    public const BOOKING_STATUS_CREATED = 'created';
    public const BOOKING_STATUS_COMPLETE = 'complete';
    public const BOOKING_STATUS_CANCELLED = 'cancelled';

    private const VALID_VALUES = [
        self::BOOKING_STATUS_CREATED,
        self::BOOKING_STATUS_COMPLETE,
        self::BOOKING_STATUS_CANCELLED,
    ];

    public const BOOKING_FINAL_STATUSES = [
        self::BOOKING_STATUS_COMPLETE,
        self::BOOKING_STATUS_CANCELLED,
    ];

    public function getName(): string
    {
        return 'booking_status';
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, self::VALID_VALUES)) {
            throw new \InvalidArgumentException(sprintf('Invalid %s value', $this->getName()));
        }

        return $value;
    }
}

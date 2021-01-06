<?php

declare(strict_types=1);

namespace App\Constraint;

class AvailabilityTypeConstraint extends AbstractChoiceConstraint
{
    public const AVAILABILITY_TYPE_ON_REQUEST = 'on-request';
    public const AVAILABILITY_TYPE_INSTANT = 'instant';

    public const VALID_VALUES = [
        self::AVAILABILITY_TYPE_ON_REQUEST,
        self::AVAILABILITY_TYPE_INSTANT,
    ];

    public static function isValid(?string $value): bool
    {
        if (empty($value)) {
            return false;
        }

        return parent::isValid($value);
    }
}

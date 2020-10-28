<?php

declare(strict_types=1);

namespace App\Constraint;

class ProductDurationUnitConstraint extends AbstractChoiceConstraint
{
    public const MINIMUM_DURATION = 1;
    public const PRODUCT_DURATION_UNIT_MINUTES = 'Minutes';
    public const PRODUCT_DURATION_UNIT_HOURS = 'Hours';
    public const PRODUCT_DURATION_UNIT_DAYS = 'Days';
    public const PRODUCT_DURATION_UNIT_NIGHTS = 'Nights';

    public const VALID_VALUES = [
        self::PRODUCT_DURATION_UNIT_MINUTES,
        self::PRODUCT_DURATION_UNIT_HOURS,
        self::PRODUCT_DURATION_UNIT_DAYS,
        self::PRODUCT_DURATION_UNIT_NIGHTS,
    ];
}

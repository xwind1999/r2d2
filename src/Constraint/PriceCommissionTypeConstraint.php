<?php

declare(strict_types=1);

namespace App\Constraint;

class PriceCommissionTypeConstraint extends AbstractChoiceConstraint
{
    public const PRICE_COMMISSION_TYPE_PERCENTAGE = 'percentage';
    public const PRICE_COMMISSION_TYPE_AMOUNT = 'amount';

    public const VALID_VALUES = [
        self::PRICE_COMMISSION_TYPE_PERCENTAGE,
        self::PRICE_COMMISSION_TYPE_AMOUNT,
    ];
}

<?php

declare(strict_types=1);

namespace App\Constraint;

class ProductStatusConstraint extends AbstractChoiceConstraint
{
    public const PRODUCT_STATUS_PROSPECT = 'prospect';
    public const PRODUCT_STATUS_PRODUCTION = 'production';
    public const PRODUCT_STATUS_LIVE = 'live';
    public const PRODUCT_STATUS_OBSOLETE = 'obsolete';
    public const PRODUCT_STATUS_ACTIVE = 'active';
    public const PRODUCT_STATUS_INACTIVE = 'inactive';
    public const PRODUCT_STATUS_REDEEMABLE = 'redeemable';
    public const PRODUCT_STATUS_READY = 'ready';

    public const VALID_VALUES = [
        self::PRODUCT_STATUS_PROSPECT,
        self::PRODUCT_STATUS_PRODUCTION,
        self::PRODUCT_STATUS_LIVE,
        self::PRODUCT_STATUS_OBSOLETE,
        self::PRODUCT_STATUS_ACTIVE,
        self::PRODUCT_STATUS_INACTIVE,
        self::PRODUCT_STATUS_REDEEMABLE,
        self::PRODUCT_STATUS_READY,
    ];
}

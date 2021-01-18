<?php

declare(strict_types=1);

namespace App\Constraint;

class RoomStockTypeConstraint extends AbstractChoiceConstraint
{
    public const ROOM_STOCK_TYPE_ALLOTMENT = 'allotment';
    public const ROOM_STOCK_TYPE_STOCK = 'stock';
    public const ROOM_STOCK_TYPE_ON_REQUEST = 'on_request';

    public const VALID_VALUES = [
        self::ROOM_STOCK_TYPE_ALLOTMENT,
        self::ROOM_STOCK_TYPE_STOCK,
        self::ROOM_STOCK_TYPE_ON_REQUEST,
    ];

    public const INSTANT_TYPES = [
        self::ROOM_STOCK_TYPE_ALLOTMENT,
        self::ROOM_STOCK_TYPE_STOCK,
    ];

    public const ON_REQUEST_TYPES = [
        self::ROOM_STOCK_TYPE_ON_REQUEST,
    ];

    public static function isInstantType(?string $value): bool
    {
        return in_array($value, self::INSTANT_TYPES, true);
    }

    public static function isOnRequestType(?string $value): bool
    {
        return in_array($value, self::ON_REQUEST_TYPES, true);
    }
}

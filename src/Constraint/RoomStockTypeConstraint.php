<?php

declare(strict_types=1);

namespace App\Constraint;

class RoomStockTypeConstraint extends AbstractChoiceConstraint
{
    public const ROOM_STOCK_TYPE_ALLOTMENT = 'allotment';
    public const ROOM_STOCK_TYPE_STOCK = 'stock';
    public const ROOM_STOCK_TYPE_ONREQUEST = 'on_request';

    public const VALID_VALUES = [
        self::ROOM_STOCK_TYPE_ALLOTMENT,
        self::ROOM_STOCK_TYPE_STOCK,
        self::ROOM_STOCK_TYPE_ONREQUEST,
    ];
}

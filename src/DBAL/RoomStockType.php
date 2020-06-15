<?php

declare(strict_types=1);

namespace App\DBAL;

use App\Constraint\RoomStockTypeConstraint;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class RoomStockType extends StringType
{
    public function getName(): string
    {
        return 'room_stock_type';
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null !== $value && !RoomStockTypeConstraint::isValid($value)) {
            throw new \InvalidArgumentException(sprintf('Invalid %s value', $this->getName()));
        }

        return $value;
    }
}

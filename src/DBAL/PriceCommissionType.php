<?php

declare(strict_types=1);

namespace App\DBAL;

use App\Constraint\PriceCommissionTypeConstraint;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class PriceCommissionType extends AbstractCommentedStringType
{
    public function getName(): string
    {
        return 'price_commission_type';
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null !== $value && !PriceCommissionTypeConstraint::isValid($value)) {
            throw new \InvalidArgumentException(sprintf('Invalid %s value', $this->getName()));
        }

        return $value;
    }
}

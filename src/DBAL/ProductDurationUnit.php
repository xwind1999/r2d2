<?php

declare(strict_types=1);

namespace App\DBAL;

use App\Constraint\ProductDurationUnitConstraint;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class ProductDurationUnit extends StringType
{
    public function getName(): string
    {
        return 'product_duration_unit';
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null !== $value && !ProductDurationUnitConstraint::isValid($value)) {
            throw new \InvalidArgumentException(sprintf('Invalid %s value', $this->getName()));
        }

        return $value;
    }
}

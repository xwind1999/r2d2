<?php

declare(strict_types=1);

namespace App\DBAL;

use App\Constraint\ProductDurationUnitConstraint;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class ProductDurationUnit extends AbstractCommentedStringType
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

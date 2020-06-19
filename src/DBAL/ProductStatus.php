<?php

declare(strict_types=1);

namespace App\DBAL;

use App\Constraint\ProductStatusConstraint;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class ProductStatus extends AbstractCommentedStringType
{
    public function getName(): string
    {
        return 'product_status';
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null !== $value && !ProductStatusConstraint::isValid($value)) {
            throw new \InvalidArgumentException(sprintf('Invalid %s value', $this->getName()));
        }

        return $value;
    }
}

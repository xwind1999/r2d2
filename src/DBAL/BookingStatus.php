<?php

declare(strict_types=1);

namespace App\DBAL;

use App\Constraint\BookingStatusConstraint;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class BookingStatus extends AbstractCommentedStringType
{
    public function getName(): string
    {
        return 'booking_status';
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null !== $value && !BookingStatusConstraint::isValid($value)) {
            throw new \InvalidArgumentException(sprintf('Invalid %s value', $this->getName()));
        }

        return $value;
    }
}

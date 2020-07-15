<?php

declare(strict_types=1);

namespace App\DBAL;

use App\Constraint\PartnerStatusConstraint;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class PartnerStatus extends AbstractCommentedStringType
{
    public function getName(): string
    {
        return 'partner_status';
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null !== $value && !PartnerStatusConstraint::isValid($value)) {
            throw new \InvalidArgumentException(sprintf('Invalid %s value', $this->getName()));
        }

        return $value;
    }
}

<?php

declare(strict_types=1);

namespace App\DBAL;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

abstract class AbstractCommentedStringType extends StringType
{
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}

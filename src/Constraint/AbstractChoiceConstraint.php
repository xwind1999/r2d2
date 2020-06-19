<?php

declare(strict_types=1);

namespace App\Constraint;

abstract class AbstractChoiceConstraint
{
    public const VALID_VALUES = [];

    public static function isValid(string $value): bool
    {
        return in_array($value, static::VALID_VALUES);
    }
}

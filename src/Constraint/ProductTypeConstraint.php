<?php

declare(strict_types=1);

namespace App\Constraint;

class ProductTypeConstraint extends AbstractChoiceConstraint
{
    public const EXPERIENCE = 'EXPERIENCE';
    public const COMPONENT = 'COMPONENT';
    public const VALID_VALUES = self::BOX_TYPES;
    private const BOX_TYPES = [
        'MEV',
        'DEV',
        'MLV',
    ];
}

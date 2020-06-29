<?php

declare(strict_types=1);

namespace App\Constraint;

class RelationshipTypeConstraint extends AbstractChoiceConstraint
{
    public const EXPERIENCE_COMPONENT = 'EXPERIENCE-COMPONENT';
    public const BOX_EXPERIENCE = 'BOX-EXPERIENCE';
}

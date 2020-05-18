<?php

declare(strict_types=1);

namespace App\Exception\Repository;

class BoxExperienceRelationshipNotFoundException extends EntityNotFoundException
{
    protected const MESSAGE = 'Box-experience relationship not found';
    protected const CODE = 1000021;
}

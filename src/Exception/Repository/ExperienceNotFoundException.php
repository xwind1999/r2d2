<?php

declare(strict_types=1);

namespace App\Exception\Repository;

class ExperienceNotFoundException extends EntityNotFoundException
{
    protected const CODE = 1000015;
}

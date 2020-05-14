<?php

declare(strict_types=1);

namespace App\Exception\Repository;

class ExperienceComponentNotFoundException extends EntityNotFoundException
{
    protected const MESSAGE = 'Experience/Component relationship not found';
    protected const CODE = 1000014;
}

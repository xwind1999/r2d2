<?php

declare(strict_types=1);

namespace App\Exception\Repository;

class PartnerNotFoundException extends EntityNotFoundException
{
    protected const MESSAGE = 'Resource (Partner) Not Found';
    protected const CODE = 1000016;
}

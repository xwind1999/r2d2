<?php

declare(strict_types=1);

namespace App\Exception\Helper;

use App\Exception\ContextualException;

class InvalidDatesForPeriod extends ContextualException
{
    protected const MESSAGE = 'Invalid dates to create date period';
    protected const CODE = 1300013;
}

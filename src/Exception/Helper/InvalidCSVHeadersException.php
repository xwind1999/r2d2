<?php

declare(strict_types=1);

namespace App\Exception\Helper;

use App\Exception\ContextualException;

class InvalidCSVHeadersException extends ContextualException
{
    protected const MESSAGE = 'CSV headers are invalid';
    protected const CODE = 1400001;
}

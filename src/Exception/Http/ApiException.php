<?php

declare(strict_types=1);

namespace App\Exception\Http;

use App\Exception\ContextualException;

class ApiException extends ContextualException
{
    protected const MESSAGE = 'Internal server error';
    protected const CODE = 1000001;
    protected const HTTP_CODE = 500;

    public function getStatusCode(): int
    {
        return static::HTTP_CODE;
    }
}
